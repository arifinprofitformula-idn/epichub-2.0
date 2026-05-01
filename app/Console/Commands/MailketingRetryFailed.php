<?php

namespace App\Console\Commands;

use App\Models\EmailNotificationLog;
use App\Services\Notifications\EmailRetryService;
use App\Services\Settings\AppSettingService;
use Illuminate\Console\Command;

class MailketingRetryFailed extends Command
{
    protected $signature = 'mailketing:retry-failed {--limit=50 : Maksimal jumlah log failed yang diproses}';

    protected $description = 'Retry email failed dari email_notification_logs dengan batas retry yang aman';

    public function handle(EmailRetryService $retryService, AppSettingService $settings): int
    {
        if (! (bool) $settings->getMailketing('enable_retry_failed_email', false)) {
            $this->warn('Retry failed email dinonaktifkan di settings.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $logs = EmailNotificationLog::query()
            ->where('status', 'failed')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->info('Tidak ada email failed untuk di-retry.');

            return self::SUCCESS;
        }

        $retried = 0;
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            $metadata = is_array($log->metadata) ? $log->metadata : [];
            $retryCount = (int) ($metadata['retry_count'] ?? 0);
            $maxRetry = max(1, (int) ($metadata['max_retry'] ?? 3));

            if ($retryCount >= $maxRetry) {
                $skipped++;
                $this->line("Skip #{$log->id} ({$log->recipient_email}) - batas retry tercapai.");
                continue;
            }

            $result = $retryService->retry($log);
            $retried++;

            if ($result['success']) {
                $sent++;
                $this->info("Retry sukses #{$log->id} -> {$log->recipient_email}");
            } else {
                $failed++;
                $this->error("Retry gagal #{$log->id} -> {$log->recipient_email}: {$result['message']}");
            }
        }

        $this->newLine();
        $this->table(
            ['diproses', 'sukses', 'gagal', 'skip'],
            [[$retried, $sent, $failed, $skipped]],
        );

        return self::SUCCESS;
    }
}
