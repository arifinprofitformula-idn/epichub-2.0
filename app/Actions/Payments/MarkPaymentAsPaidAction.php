<?php

namespace App\Actions\Payments;

use App\Actions\Access\GrantOrderAccessAction;
use App\Actions\Affiliates\CreateCommissionsForOrderAction;
use App\Actions\Event\RegisterOrderEventsAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MarkPaymentAsPaidAction
{
    public function __construct(
        protected GrantOrderAccessAction $grantOrderAccess,
        protected RegisterOrderEventsAction $registerOrderEvents,
        protected CreateCommissionsForOrderAction $createCommissionsForOrder,
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

            // Catatan: future payment gateway callback harus memakai alur yang sama agar grant akses konsisten dan idempotent.

            return $payment->refresh();
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
}

