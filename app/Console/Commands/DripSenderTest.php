<?php

namespace App\Console\Commands;

use App\Services\DripSender\DripSenderClient;
use Illuminate\Console\Command;

class DripSenderTest extends Command
{
    protected $signature = 'dripsender:test';

    protected $description = 'Cek setting dan koneksi DripSender melalui endpoint GET /lists/';

    public function handle(DripSenderClient $client): int
    {
        if (! $client->isEnabled()) {
            $this->warn('DripSender tidak diaktifkan. Aktifkan dari Admin -> Settings -> WhatsApp Integration / DripSender.');

            return self::FAILURE;
        }

        $validation = $client->validateSettings();

        if (! $validation['ok']) {
            $this->error($validation['message']);

            return self::FAILURE;
        }

        $result = $client->getLists();

        if (! $result['success']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $lists = $result['lists'] ?? [];

        $this->info('Koneksi DripSender berhasil.');
        $this->line('Jumlah list: '.count($lists));

        if ($lists !== []) {
            $this->table(
                ['List ID', 'List Name', 'Contact Count'],
                collect($lists)->map(fn (array $item): array => [
                    (string) ($item['id'] ?? $item['list_id'] ?? '-'),
                    (string) ($item['name'] ?? $item['list_name'] ?? '-'),
                    (string) ($item['contact_count'] ?? $item['total_contact'] ?? '-'),
                ])->all(),
            );
        }

        return self::SUCCESS;
    }
}
