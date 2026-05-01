<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\WhatsAppNotificationLog;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use App\Services\Settings\AppSettingService;
use Illuminate\Console\Command;

class SendWhatsAppPaymentReminders extends Command
{
    protected $signature = 'whatsapp:send-payment-reminders';

    protected $description = 'Kirim reminder pembayaran via WhatsApp untuk pembayaran pending';

    public function handle(
        AppSettingService $settings,
        WhatsAppNotificationService $service,
        WhatsAppMessageTemplateService $templates,
    ): int {
        if (! (bool) $settings->getDripSender('enable_whatsapp_payment_reminder', false)) {
            $this->warn('WhatsApp payment reminder dinonaktifkan.');

            return self::SUCCESS;
        }

        $afterHours = max(1, (int) $settings->getDripSender('payment_reminder_after_hours', 24));
        $eligibleFrom = now()->subHours($afterHours);
        $sent = 0;

        $payments = Payment::query()
            ->with(['order.user'])
            ->where('payment_method', PaymentMethod::ManualBankTransfer)
            ->where('status', PaymentStatus::Pending)
            ->where('created_at', '<=', $eligibleFrom)
            ->whereHas('order', fn ($query) => $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Unpaid]))
            ->get();

        foreach ($payments as $payment) {
            $user = $payment->order?->user;

            if (! $user) {
                continue;
            }

            $sentCount = WhatsAppNotificationLog::query()
                ->where('event_type', 'payment_reminder')
                ->where('notifiable_type', Payment::class)
                ->where('notifiable_id', $payment->id)
                ->where('status', 'sent')
                ->count();

            if ($sentCount >= 2) {
                continue;
            }

            $message = $templates->render('payment_reminder', [
                'name' => $user->name,
                'order_number' => $payment->order?->order_number,
                'total_amount' => 'Rp '.number_format((float) $payment->amount, 0, ',', '.'),
                'payment_url' => route('payments.show', $payment),
            ]);

            $service->sendToUser($user, $message, 'payment_reminder', [
                'notifiable' => $payment,
                'reminder_attempt' => $sentCount + 1,
            ]);

            $sent++;
        }

        $this->info("Reminder pembayaran WhatsApp diproses: {$sent}");

        return self::SUCCESS;
    }
}
