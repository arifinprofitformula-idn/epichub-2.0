<?php

namespace App\Console\Commands;

use App\Models\MailketingList;
use App\Services\Mailketing\MailketingClient;
use Illuminate\Console\Command;

class MailketingSyncLists extends Command
{
    protected $signature = 'mailketing:sync-lists';

    protected $description = 'Sync daftar list dari Mailketing ke database lokal';

    public function handle(MailketingClient $client): int
    {
        if (! $client->isEnabled()) {
            $this->warn('Mailketing tidak diaktifkan.');
            return self::FAILURE;
        }

        $this->info('Mengambil list dari Mailketing...');

        $result = $client->getLists();

        if (! $result['success']) {
            $this->error('Gagal: '.$result['message']);
            return self::FAILURE;
        }

        $lists = $result['lists'] ?? [];
        $now   = now();
        $count = 0;

        foreach ($lists as $item) {
            if (empty($item['list_id'])) {
                continue;
            }

            MailketingList::updateOrCreate(
                ['list_id' => (string) $item['list_id']],
                [
                    'list_name'   => $item['list_name'] ?? '',
                    'raw_payload' => $item,
                    'synced_at'   => $now,
                ],
            );

            $count++;
        }

        $this->info("✓ Sync selesai — {$count} list disimpan.");

        if ($count > 0) {
            $this->table(
                ['List ID', 'List Name'],
                array_map(fn ($l) => [$l['list_id'] ?? '-', $l['list_name'] ?? '-'], $lists)
            );
        }

        return self::SUCCESS;
    }
}
