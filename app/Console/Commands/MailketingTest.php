<?php

namespace App\Console\Commands;

use App\Services\Mailketing\MailketingClient;
use App\Services\Settings\AppSettingService;
use Illuminate\Console\Command;

class MailketingTest extends Command
{
    protected $signature = 'mailketing:test';

    protected $description = 'Test koneksi ke Mailketing API dengan memanggil viewlist';

    public function handle(MailketingClient $client, AppSettingService $settings): int
    {
        $this->info('Memeriksa pengaturan Mailketing...');

        if (! $client->isEnabled()) {
            $this->warn('Mailketing tidak diaktifkan. Aktifkan melalui Admin → Settings → Mailketing.');
            return self::FAILURE;
        }

        $validation = $client->validateSettings();
        if (! $validation['ok']) {
            $this->error('Pengaturan tidak lengkap: '.$validation['message']);
            foreach ($validation['missing'] as $field) {
                $this->line("  ✗ {$field}");
            }
            return self::FAILURE;
        }

        $this->info('Menghubungi Mailketing API...');

        $result = $client->getLists();

        if ($result['success']) {
            $lists = $result['lists'] ?? [];
            $this->info('✓ Koneksi berhasil!');
            $this->line('  Jumlah list: '.count($lists));

            if (! empty($lists)) {
                $this->newLine();
                $this->table(['List ID', 'List Name'], array_map(
                    fn ($l) => [$l['list_id'] ?? '-', $l['list_name'] ?? '-'],
                    $lists
                ));
            }

            return self::SUCCESS;
        }

        $this->error('✗ Koneksi gagal: '.$result['message']);
        return self::FAILURE;
    }
}
