<?php

namespace App\Console\Commands;

use App\Services\Mailketing\MailketingClient;
use App\Services\Settings\AppSettingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MailketingSendTest extends Command
{
    protected $signature = 'mailketing:send-test {email : Alamat email penerima}';

    protected $description = 'Kirim test email melalui Mailketing ke alamat yang ditentukan';

    public function handle(MailketingClient $client, AppSettingService $settings): int
    {
        $email = (string) $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Alamat email tidak valid: {$email}");
            return self::FAILURE;
        }

        if (! $client->isEnabled()) {
            $this->warn('Mailketing tidak diaktifkan.');
            return self::FAILURE;
        }

        $this->info("Mengirim test email ke: {$email}");

        $appName = config('app.name', 'EPIC HUB');
        $time    = Carbon::now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i');

        $result = $client->sendEmail([
            'recipient'  => $email,
            'subject'    => "Test Email {$appName}",
            'content'    => <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
  <h2 style="color: #f59e0b;">Test Email dari {$appName}</h2>
  <p>Ini adalah email test yang dikirim dari perintah artisan <strong>mailketing:send-test</strong>.</p>
  <p>Jika Anda menerima email ini, berarti integrasi Mailketing berfungsi dengan baik.</p>
  <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">
  <p style="color: #6b7280; font-size: 12px;">Dikirim pada: {$time}</p>
</div>
HTML,
            'event_type' => 'test_email_cli',
        ]);

        if ($result['success']) {
            $this->info('✓ Test email berhasil dikirim!');
            $this->line("  Penerima : {$email}");
            $this->line("  Response : ".($result['message'] ?? '-'));
            return self::SUCCESS;
        }

        $this->error('✗ Gagal mengirim test email: '.$result['message']);
        return self::FAILURE;
    }
}
