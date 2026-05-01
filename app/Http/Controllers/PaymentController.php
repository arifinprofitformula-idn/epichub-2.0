<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\UploadPaymentProofRequest;
use App\Models\Payment;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Payment $payment): View
    {
        $payment->loadMissing(['order.items.product', 'order.user', 'verifiedBy']);

        $this->authorize('view', $payment);

        return view('payments.show', [
            'payment' => $payment,
        ]);
    }

    public function proof(Payment $payment): View|RedirectResponse
    {
        $payment->loadMissing(['order', 'verifiedBy']);

        $this->authorize('view', $payment);

        if (! $payment->proof_of_payment) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Bukti pembayaran belum tersedia.');
        }

        $path = (string) $payment->proof_of_payment;
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        $proofKind = match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'pdf' => 'pdf',
            default => 'file',
        };

        return view('payments.proof', [
            'payment' => $payment,
            'proofUrl' => Storage::disk('public')->url($path),
            'proofKind' => $proofKind,
        ]);
    }

    public function storeProof(UploadPaymentProofRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('uploadProof', $payment);

        $file = $request->file('proof');
        $path = $file->store('payment-proofs', 'public');

        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }

        $payment->update([
            'proof_of_payment' => $path,
        ]);

        $this->sendPaymentSubmittedEmails($payment->fresh(['order.items.product', 'order.user']));

        return back();
    }

    private function sendPaymentSubmittedEmails(Payment $payment): void
    {
        try {
            $order = $payment->order;
            $user  = $order?->user;

            if (! $order || ! $user) {
                return;
            }

            $emailSvc   = app(EmailNotificationService::class);
            $submittedAt = now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i');

            $emailSvc->sendTransactionalEmail(
                recipient: ['email' => $user->email, 'name' => $user->name],
                subject: 'Bukti Pembayaran Anda Sedang Diverifikasi',
                view: 'emails.orders.payment-submitted',
                data: [
                    'userName'      => $user->name,
                    'orderNumber'   => $order->order_number,
                    'paymentNumber' => $payment->payment_number,
                    'amount'        => (float) $payment->amount,
                    'paymentUrl'    => route('payments.show', $payment),
                ],
                eventType: 'payment_submitted',
                metadata: ['notifiable' => $payment],
            );

            $emailSvc->sendAdminNotification(
                subject: 'Bukti Pembayaran Baru — '.$payment->payment_number,
                view: 'emails.admin.payment-submitted',
                data: [
                    'paymentNumber' => $payment->payment_number,
                    'orderNumber'   => $order->order_number,
                    'customerName'  => $user->name,
                    'customerEmail' => $user->email,
                    'amount'        => (float) $payment->amount,
                    'submittedAt'   => $submittedAt,
                    'adminPaymentUrl' => url('/admin/payments/'.$payment->payment_number.'/edit'),
                ],
                eventType: 'admin_payment_submitted',
                metadata: ['notifiable' => $payment],
            );

            $whatsAppSvc = app(WhatsAppNotificationService::class);
            $templateSvc = app(WhatsAppMessageTemplateService::class);
            $amount = 'Rp '.number_format((float) $payment->amount, 0, ',', '.');

            $whatsAppSvc->sendToUser(
                user: $user,
                message: $templateSvc->render('payment_submitted', [
                    'name' => $user->name,
                    'payment_number' => $payment->payment_number,
                ]),
                eventType: 'payment_submitted',
                metadata: ['notifiable' => $payment],
            );

            $whatsAppSvc->sendAdminAlert(
                message: $templateSvc->render('admin_payment_submitted', [
                    'payment_number' => $payment->payment_number,
                    'member_name' => $user->name,
                    'amount' => $amount,
                ]),
                eventType: 'admin_payment_submitted',
                metadata: ['notifiable' => $payment],
            );
        } catch (\Throwable $e) {
            Log::error('PaymentController: gagal kirim payment submitted notification', ['error' => $e->getMessage()]);
        }
    }
}
