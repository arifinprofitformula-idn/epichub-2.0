<?php

namespace App\Http\Controllers;

use App\Actions\Affiliates\ResolveCurrentReferralAction;
use App\Actions\Checkout\CreateGuestCheckoutUserAction;
use App\Actions\Event\CheckEventCheckoutEligibilityAction;
use App\Actions\Orders\CreateDirectOrderAction;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Mailketing\MailketingSubscriberService;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationPayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function show(
        Request $request,
        Product $product,
        CheckEventCheckoutEligibilityAction $checkEventCheckoutEligibility,
        ResolveCurrentReferralAction $resolveCurrentReferral,
    ): View
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->firstOrFail();

        $unitPrice = (float) $product->effective_price;
        $isEligible = $unitPrice > 0;
        $eligibilityMessage = $isEligible ? null : 'Produk ini belum tersedia untuk checkout saat ini.';

        $type = $product->product_type instanceof ProductType ? $product->product_type->value : (string) $product->product_type;

        if ($isEligible && $type === ProductType::Event->value) {
            try {
                $checkEventCheckoutEligibility->execute($product);
            } catch (RuntimeException $e) {
                $isEligible = false;
                $eligibilityMessage = $e->getMessage();
            }
        }

        return view($request->user() ? 'checkout.show-app' : 'checkout.show-public', [
            'product' => $product,
            'isEligible' => $isEligible,
            'eligibilityMessage' => $eligibilityMessage,
            'referralInfo' => $resolveCurrentReferral->execute($request),
        ]);
    }

    public function store(
        Request $request,
        Product $product,
        CreateDirectOrderAction $action,
        CreateGuestCheckoutUserAction $createGuestCheckoutUser,
    ): RedirectResponse
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->firstOrFail();

        try {
            $guestUserCreated = false;

            $payment = DB::transaction(function () use ($request, $product, $action, $createGuestCheckoutUser, &$guestUserCreated) {
                $user = $request->user();

                if (! $user) {
                    $user = $createGuestCheckoutUser->execute($request->validate([
                        'name' => ['required', 'string', 'max:255'],
                        'email' => ['required', 'string', 'email', 'max:255'],
                        'whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s\(\)]+$/'],
                        'password' => ['required'],
                        'password_confirmation' => ['required'],
                    ]), $request);

                    $guestUserCreated = true;
                }

                return [
                    'payment' => $action->execute($user, $product, $request),
                    'user' => $user,
                ];
            });
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'checkout' => $e->getMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        } catch (ValidationException $e) {
            throw $e;
        }

        if ($guestUserCreated) {
            Auth::login($payment['user']);
            $request->session()->regenerate();
            $this->sendGuestWelcomeEmail($payment['user']);
        }

        $this->sendOrderCreatedEmails($payment['user'], $payment['payment']);

        return redirect()->route('payments.show', $payment['payment']);
    }

    private function sendGuestWelcomeEmail(User $user): void
    {
        try {
            $payload    = app(NotificationPayloadBuilder::class)->forUserRegistered($user);
            $dispatcher = app(NotificationDispatcher::class);

            $dispatcher->notifyMemberEmail(
                eventKey:   'user_registered',
                user:       $user,
                payload:    $payload,
                notifiable: $user,
                fallback:   fn () => app(EmailNotificationService::class)->sendTransactionalEmail(
                    recipient: ['email' => $user->email, 'name' => $user->name],
                    subject:   'Selamat Datang di EPIC HUB',
                    view:      'emails.auth.welcome',
                    data:      [
                        'userName'     => $user->name,
                        'userEmail'    => $user->email,
                        'dashboardUrl' => url('/dashboard'),
                        'productsUrl'  => url('/produk-saya'),
                    ],
                    eventType: 'user_registered',
                    metadata:  ['notifiable' => $user],
                ),
            );

            $dispatcher->notifyMemberWhatsApp(
                eventKey:   'user_registered',
                user:       $user,
                payload:    $payload,
                notifiable: $user,
                legacyData: ['name' => $user->name, 'dashboard_url' => url('/dashboard')],
            );
        } catch (\Throwable $e) {
            Log::error('CheckoutController: gagal kirim welcome notification', ['error' => $e->getMessage()]);
        }

        try {
            app(MailketingSubscriberService::class)->addUserToDefaultList($user);
        } catch (\Throwable $e) {
            Log::error('CheckoutController: gagal subscriber automation default list', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function sendOrderCreatedEmails(User $user, Payment $payment): void
    {
        try {
            $payment->loadMissing(['order.items.product']);
            $order = $payment->order;

            if (! $order) {
                return;
            }

            $paymentUrl  = route('payments.show', $payment);
            $methodLabel = $payment->payment_method instanceof PaymentMethod
                ? $payment->payment_method->value
                : (string) $payment->payment_method;

            $products = $order->items->map(fn ($item) => $item->product?->name ?? '-')->filter()->values()->all();
            $amount   = 'Rp '.number_format((float) $order->total_amount, 0, ',', '.');

            $payload    = app(NotificationPayloadBuilder::class)->forOrderCreated($order);
            $dispatcher = app(NotificationDispatcher::class);
            $emailSvc   = app(EmailNotificationService::class);

            // ── Member ────────────────────────────────────────────────────
            $dispatcher->notifyMemberEmail(
                eventKey:   'order_created',
                user:       $user,
                payload:    $payload,
                notifiable: $order,
                fallback:   fn () => $emailSvc->sendTransactionalEmail(
                    recipient: ['email' => $user->email, 'name' => $user->name],
                    subject:   'Order Anda Berhasil Dibuat — '.$order->order_number,
                    view:      'emails.orders.created',
                    data:      [
                        'userName'      => $user->name,
                        'orderNumber'   => $order->order_number,
                        'products'      => $products,
                        'totalAmount'   => (float) $order->total_amount,
                        'paymentMethod' => $methodLabel,
                        'paymentUrl'    => $paymentUrl,
                    ],
                    eventType: 'order_created',
                    metadata:  ['notifiable' => $order],
                ),
            );

            $dispatcher->notifyMemberWhatsApp(
                eventKey:   'order_created',
                user:       $user,
                payload:    $payload,
                notifiable: $order,
                legacyData: [
                    'name'         => $user->name,
                    'order_number' => $order->order_number,
                    'total_amount' => $amount,
                    'payment_url'  => $paymentUrl,
                ],
            );

            // ── Admin ─────────────────────────────────────────────────────
            $adminPayload = app(NotificationPayloadBuilder::class)->forAdminOrder($order);

            $dispatcher->notifyAdminEmail(
                eventKey:   'admin_order_created',
                payload:    $adminPayload,
                notifiable: $order,
                fallback:   fn () => $emailSvc->sendAdminNotification(
                    subject:   'Order Baru Masuk — '.$order->order_number,
                    view:      'emails.admin.new-order',
                    data:      [
                        'orderNumber'   => $order->order_number,
                        'customerName'  => $user->name,
                        'customerEmail' => $user->email,
                        'products'      => $products,
                        'totalAmount'   => (float) $order->total_amount,
                        'paymentMethod' => $methodLabel,
                        'createdAt'     => $order->created_at?->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i') ?? '-',
                        'adminOrderUrl' => url('/admin/orders/'.$order->order_number.'/edit'),
                    ],
                    eventType: 'admin_order_created',
                    metadata:  ['notifiable' => $order],
                ),
            );

            $dispatcher->notifyAdminWhatsApp(
                eventKey:   'admin_order_created',
                payload:    $adminPayload,
                notifiable: $order,
                legacyData: [
                    'order_number' => $order->order_number,
                    'member_name'  => $user->name,
                    'total_amount' => $amount,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('CheckoutController: gagal kirim order notification', ['error' => $e->getMessage()]);
        }
    }
}
