<?php

namespace App\Jobs;

use App\Models\WhatsAppNotificationLog;
use App\Services\Notifications\WhatsAppNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDripSenderWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 60, 60];

    public function __construct(
        public int $logId,
    ) {}

    public function handle(WhatsAppNotificationService $service): void
    {
        $log = WhatsAppNotificationLog::query()->find($this->logId);

        if (! $log) {
            return;
        }

        $service->deliverLog($log);
    }
}
