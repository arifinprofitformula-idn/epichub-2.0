<?php

namespace App\Console\Commands;

use App\Models\DripsenderList;
use App\Services\DripSender\DripSenderClient;
use Illuminate\Console\Command;

class DripSenderSyncLists extends Command
{
    protected $signature = 'dripsender:sync-lists';

    protected $description = 'Ambil daftar list dari DripSender dan simpan ke cache lokal';

    public function handle(DripSenderClient $client): int
    {
        if (! $client->isEnabled()) {
            $this->warn('DripSender tidak diaktifkan.');

            return self::FAILURE;
        }

        $result = $client->getLists();

        if (! $result['success']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $count = 0;
        $now = now();

        foreach ($result['lists'] as $item) {
            $listId = (string) ($item['id'] ?? $item['list_id'] ?? '');

            if ($listId === '') {
                continue;
            }

            DripsenderList::query()->updateOrCreate(
                ['list_id' => $listId],
                [
                    'list_name' => (string) ($item['name'] ?? $item['list_name'] ?? ''),
                    'contact_count' => data_get($item, 'contact_count') ?? data_get($item, 'total_contact'),
                    'raw_payload' => $item,
                    'synced_at' => $now,
                ],
            );

            $count++;
        }

        $this->info("Sync selesai. {$count} list disimpan.");

        return self::SUCCESS;
    }
}
