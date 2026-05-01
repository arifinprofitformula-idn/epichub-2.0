<?php

namespace App\Console\Commands;

use App\Models\WhatsAppNotificationLog;
use App\Services\Notifications\WhatsAppRetryService;
use Illuminate\Console\Command;

class DripSenderRetryFailed extends Command
{
    protected $signature = 'dripsender:retry-failed {--limit=50 : Maksimal jumlah failed log yang diproses}';

    protected $description = 'Retry pesan WhatsApp gagal dengan batas maksimal 3 kali';

    public function handle(WhatsAppRetryService $retryService): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $logs = WhatsAppNotificationLog::query()
            ->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->info('Tidak ada pesan WhatsApp failed untuk di-retry.');

            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($logs as $log) {
            $result = $retryService->retry($log);

            if ($result['success']) {
                $success++;
                $this->info("Retry sukses untuk log #{$log->id}");
            } else {
                $failed++;
                $this->error("Retry gagal untuk log #{$log->id}: {$result['message']}");
            }
        }

        $this->table(['sukses', 'gagal'], [[$success, $failed]]);

        return self::SUCCESS;
    }
}
