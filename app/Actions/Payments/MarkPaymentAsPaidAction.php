<?php

namespace App\Actions\Payments;

use App\Actions\Access\GrantOrderAccessAction;
use App\Actions\Affiliates\CreateCommissionsForOrderAction;
use App\Actions\Contributors\CreateContributorCommissionsForOrderAction;
use App\Actions\Event\RegisterOrderEventsAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Notifications\EmailNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MarkPaymentAsPaidAction
{
    public function __construct(
        protected GrantOrderAccessAction $grantOrderAccess,
        protected RegisterOrderEventsAction $registerOrderEvents,
        protected CreateCommissionsForOrderAction $createCommissionsForOrder,
        protected CreateContributorCommissionsForOrderAction $createContributorCommissionsForOrder,
    ) {
    }

    public function execute(Payment $payment, User $verifiedBy): Payment
    {
        return DB::transaction(function () use ($payment, $verifiedBy): Payment {
            $payment = Payment::query()
                ->with('order')
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $order = Order::query()
                ->whereKey($payment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->payment_method !== PaymentMethod::ManualBankTransfer) {
                throw new RuntimeException('Metode pembayaran tidak didukung untuk verifikasi manual.');
            }

            if ($payment->status === PaymentStatus::Success) {
                if ($order->status !== OrderStatus::Paid) {
                    throw new RuntimeException('Payment sudah success, tetapi status order belum paid.');
                }

                $this->grantOrderAccess->execute($order, $verifiedBy);
                $this->registerOrderEvents->execute($order, $verifiedBy);
                $this->tryCreateCommissions($order);
                $this->tryCreateContributorCommissions($order);

                return $payment->refresh();
            }

            if ($payment->status !== PaymentStatus::Pending) {
                throw new RuntimeException('Payment tidak dalam status pending.');
            }

            $now = now();

            $payment->update([
                'status' => PaymentStatus::Success,
                'paid_at' => $now,
                'verified_by' => $verifiedBy->id,
                'verified_at' => $now,
            ]);

            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => $now,
            ]);

            $this->grantOrderAccess->execute($order, $verifiedBy);
            $this->registerOrderEvents->execute($order, $verifiedBy);
            $this->tryCreateCommissions($order);
            $this->tryCreateContributorCommissions($order);

            // Catatan: future payment gateway callback harus memakai alur yang sama agar grant akses konsisten dan idempotent.

            $this->sendPaymentApprovedEmail($payment->refresh(), $order);

            return $payment;
        });
    }

    protected function tryCreateCommissions(Order $order): void
    {
        try {
            $this->createCommissionsForOrder->execute($order);
        } catch (\Throwable $e) {
            Log::error('Failed to create commissions for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function tryCreateContributorCommissions(Order $order): void
    {
        try {
            $this->createContributorCommissionsForOrder->execute($order);
        } catch (\Throwable $e) {
            Log::error('Failed to create contributor commissions for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendPaymentApprovedEmail(Payment $payment, Order $order): void
    {
        try {
            $order->loadMissing(['items.product', 'user']);
            $user = $order->user;

            if (! $user) {
                return;
            }

            $products = $order->items->map(fn ($item) => $item->product?->name ?? '-')->filter()->values()->all();

            $hasCourseProd = $order->items->contains(fn ($item) => $this->isProductType($item->product, ProductType::Course));
            $hasEventProd  = $order->items->contains(fn ($item) => $this->isProductType($item->product, ProductType::Event));

            app(EmailNotificationService::class)->sendTransactionalEmail(
                recipient: ['email' => $user->email, 'name' => $user->name],
                subject: 'Pembayaran Berhasil, Akses Produk Aktif!',
                view: 'emails.orders.payment-approved',
                data: [
                    'userName'      => $user->name,
                    'orderNumber'   => $order->order_number,
                    'products'      => $products,
                    'myProductsUrl' => url('/produk-saya'),
                    'myCoursesUrl'  => $hasCourseProd ? url('/kelas-saya') : null,
                    'myEventsUrl'   => $hasEventProd ? url('/event-saya') : null,
                ],
                eventType: 'payment_approved',
                metadata: ['notifiable' => $payment],
            );
        } catch (\Throwable $e) {
            Log::error('MarkPaymentAsPaidAction: gagal kirim payment approved email', ['error' => $e->getMessage()]);
        }
    }

    private function isProductType(mixed $product, ProductType $type): bool
    {
        if (! $product) {
            return false;
        }
        $pt = $product->product_type;
        return ($pt instanceof ProductType ? $pt : ProductType::tryFrom((string) $pt)) === $type;
    }
}

