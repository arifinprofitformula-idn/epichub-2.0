<?php

namespace App\Console\Commands;

use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use Illuminate\Console\Command;

class DripSenderSendTest extends Command
{
    protected $signature = 'dripsender:send-test {phone : Nomor WhatsApp tujuan}';

    protected $description = 'Kirim pesan test WhatsApp melalui DripSender';

    public function handle(
        WhatsAppNotificationService $service,
        WhatsAppMessageTemplateService $templateService,
    ): int {
        $phone = (string) $this->argument('phone');
        $message = $templateService->render('test_whatsapp_cli', [
            'sent_at' => now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i'),
        ]);

        $log = $service->sendToPhone($phone, $message, 'test_whatsapp_cli', [
            'recipient_name' => 'CLI Test',
        ]);

        if (! $log) {
            $this->error('Gagal membuat log pengiriman test WhatsApp.');

            return self::FAILURE;
        }

        $log->refresh();

        if ($log->status === 'sent') {
            $this->info('Test WhatsApp berhasil dikirim.');

            return self::SUCCESS;
        }

        $this->error($log->error_message ?: 'Test WhatsApp gagal.');

        return self::FAILURE;
    }
}
