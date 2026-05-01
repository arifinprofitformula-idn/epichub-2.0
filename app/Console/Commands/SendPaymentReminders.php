<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\EmailNotificationLog;
use App\Models\Payment;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Settings\AppSettingService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'notifications:send-payment-reminders';

    protected $description = 'Kirim reminder pembayaran untuk order pending/manual payment yang belum paid';

    public function handle(AppSettingService $settings, EmailNotificationService $emailService): int
    {
        if (! (bool) $settings->getMailketing('enable_payment_reminder', false)) {
            $this->warn('Payment reminder dinonaktifkan di settings.');

            return self::SUCCESS;
        }

        $afterHours = max(1, (int) $settings->getMailketing('payment_reminder_after_hours', 24));
        $now = now();
        $eligibleFrom = $now->copy()->subHours($afterHours);

        $payments = Payment::query()
            ->with(['order.items.product', 'order.user'])
            ->where('payment_method', PaymentMethod::ManualBankTransfer)
            ->where('status', PaymentStatus::Pending)
            ->where(function ($query): void {
                $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->whereHas('order', function ($query): void {
                $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Unpaid]);
            })
            ->where('created_at', '<=', $eligibleFrom)
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            $order = $payment->order;
            $user = $order?->user;

            if (! $order || ! $user || blank($user->email)) {
                $skipped++;
                continue;
            }

            $count = EmailNotificationLog::query()
                ->where('event_type', 'payment_reminder')
                ->where('notifiable_type', Payment::class)
                ->where('notifiable_id', $payment->id)
                ->where('status', 'sent')
                ->count();

            if ($count >= 2) {
                $skipped++;
                continue;
            }

            if ($count === 1) {
                $lastSentAt = EmailNotificationLog::query()
                    ->where('event_type', 'payment_reminder')
                    ->where('notifiable_type', Payment::class)
                    ->where('notifiable_id', $payment->id)
                    ->where('status', 'sent')
                    ->latest('sent_at')
                    ->value('sent_at');

                if ($payment->created_at->gt($now->copy()->subHours($afterHours * 2)) || ($lastSentAt && now()->diffInHours($lastSentAt) < $afterHours)) {
                    $skipped++;
                    continue;
                }
            }

            $products = $order->items->map(fn ($item) => $item->product?->title)->filter()->values()->all();

            $emailService->sendTransactionalEmail(
                recipient: ['email' => $user->email, 'name' => $user->name],
                subject: 'Pengingat Pembayaran Order EPIC HUB',
                view: 'emails.orders.payment-reminder',
                data: [
                    'userName' => $user->name,
                    'orderNumber' => $order->order_number,
                    'products' => $products,
                    'totalAmount' => (float) $order->total_amount,
                    'paymentUrl' => route('payments.show', $payment),
                    'attemptNumber' => $count + 1,
                ],
                eventType: 'payment_reminder',
                metadata: [
                    'notifiable' => $payment,
                    'reminder_attempt' => $count + 1,
                ],
            );

            $sent++;
            $this->info("Reminder pembayaran dikirim ke {$user->email} untuk {$order->order_number}");
        }

        $this->table(['sent', 'skipped'], [[$sent, $skipped]]);

        return self::SUCCESS;
    }
}
