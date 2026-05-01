<?php

namespace App\Console\Commands;

use App\Services\Notifications\NotificationPayloadBuilder;
use App\Services\Notifications\NotificationShortcodeRegistry;
use Illuminate\Console\Command;

class NotificationShortcodesCommand extends Command
{
    protected $signature = 'notifications:shortcodes
        {eventKey? : Event key untuk menampilkan shortcode relevan}
        {--target= : Filter shortcode berdasarkan target (member/sponsor/admin)}
        {--validate= : Validasi template string terhadap event yang dipilih}
        {--render= : Render template string menggunakan dummy payload}';

    protected $description = 'Tampilkan daftar shortcode notifikasi resmi EPIC HUB 2.0';

    public function __construct(
        protected NotificationShortcodeRegistry $registry,
        protected NotificationPayloadBuilder $builder,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $eventKey = $this->argument('eventKey');

        if (! $eventKey) {
            return $this->showEventList();
        }

        if (! in_array($eventKey, $this->registry->events(), true)) {
            $this->error("Event [{$eventKey}] tidak dikenal.");
            $this->line('Jalankan tanpa argumen untuk melihat daftar event yang tersedia.');
            return self::FAILURE;
        }

        $this->showShortcodesForEvent($eventKey);

        if ($this->option('validate') !== null) {
            $this->runValidate((string) $this->option('validate'), $eventKey);
        }

        if ($this->option('render') !== null) {
            $this->runRender((string) $this->option('render'), $eventKey);
        }

        return self::SUCCESS;
    }

    // ── Sections ─────────────────────────────────────────────────────────────

    private function showEventList(): int
    {
        $this->newLine();
        $this->line('<fg=yellow;options=bold>EPIC HUB 2.0 — Daftar Event Notifikasi</>');
        $this->line(str_repeat('─', 60));

        $events  = $this->registry->events();
        $grouped = $this->groupEvents($events);

        foreach ($grouped as $group => $keys) {
            $this->newLine();
            $this->line("<fg=cyan>[ {$group} ]</>");
            foreach ($keys as $key) {
                $this->line("  <fg=green>{$key}</>");
            }
        }

        $this->newLine();
        $this->line('<fg=white>Penggunaan:</>');
        $this->line('  php artisan notifications:shortcodes <eventKey>');
        $this->line('  php artisan notifications:shortcodes order_created --target=member');
        $this->line('  php artisan notifications:shortcodes order_created --validate="Halo {member_name}"');
        $this->line('  php artisan notifications:shortcodes order_created --render="Halo {member_name}, order {order_number}"');
        $this->newLine();

        return self::SUCCESS;
    }

    private function showShortcodesForEvent(string $eventKey): void
    {
        $targetKey = $this->option('target');

        $shortcodes = $targetKey
            ? $this->registry->forTarget($eventKey, $targetKey)
            : $this->registry->forEvent($eventKey);

        $this->newLine();
        $this->line("<fg=yellow;options=bold>Shortcode untuk event: {$eventKey}</>");
        if ($targetKey) {
            $canonical = $this->registry->normalizeTarget($targetKey);
            $this->line("<fg=white>Target: {$targetKey}</> <fg=gray>→ {$canonical}</>");
        }
        $this->line(str_repeat('─', 72));

        if (empty($shortcodes)) {
            $this->warn('Tidak ada shortcode untuk kombinasi event/target ini.');
            return;
        }

        $rows = array_map(fn (array $def) => [
            $def['shortcode'],
            $def['label'],
            $def['example'],
            $def['safe_for_email'] ? '✓' : '✗',
            $def['safe_for_whatsapp'] ? '✓' : '✗',
        ], $shortcodes);

        $this->table(
            ['Shortcode', 'Label', 'Contoh', 'Email', 'WA'],
            $rows,
        );

        $this->newLine();
        $this->line('<fg=white>Deskripsi lengkap:</>');
        foreach ($shortcodes as $def) {
            $this->line("  <fg=green>{$def['shortcode']}</> — {$def['description']}");
        }

        $this->newLine();
        $this->line('<fg=white>Alias lama yang didukung:</>');
        foreach ($this->registry->aliases() as $alias => $canonical) {
            $this->line("  <fg=yellow>{{$alias}}</> → {$canonical}");
        }

        // Dummy render preview
        $this->newLine();
        $this->line('<fg=white>Dummy render preview:</>');
        $dummy   = $this->builder->dummyForEvent($eventKey);
        $preview = array_map(fn (array $def) => [
            $def['shortcode'],
            $this->registry->render($def['shortcode'], $dummy, $eventKey),
        ], $shortcodes);
        $this->table(['Shortcode', 'Nilai (dummy)'], $preview);
    }

    private function runValidate(string $content, string $eventKey): void
    {
        $this->newLine();
        $this->line('<fg=yellow;options=bold>Hasil Validasi:</>');
        $this->line(str_repeat('─', 60));
        $this->line("Template : <fg=white>{$content}</>");

        $result = $this->registry->validateContent($content, $eventKey);

        $this->line('Found    : '.implode(', ', array_map(fn ($k) => "{{$k}}", $result['found_shortcodes'])));

        if ($result['deprecated_aliases'] !== []) {
            foreach ($result['deprecated_aliases'] as $alias => $canonical) {
                $this->line("<fg=yellow>  [DEPRECATED]</> {{$alias}} → {$canonical}");
            }
        }

        if ($result['invalid_shortcodes'] !== []) {
            foreach ($result['invalid_shortcodes'] as $key) {
                $suggestion = $result['suggestions'][$key] ?? null;
                $hint       = $suggestion ? " (mungkin maksud Anda: {{$suggestion}}?)" : '';
                $this->line("<fg=red>  [INVALID]</> {{$key}}{$hint}");
            }
        }

        $this->newLine();
        if ($result['valid']) {
            $this->info('✓ Template valid — semua shortcode dikenali.');
        } else {
            $this->error('✗ Template mengandung shortcode tidak valid.');
        }
    }

    private function runRender(string $content, string $eventKey): void
    {
        $this->newLine();
        $this->line('<fg=yellow;options=bold>Hasil Render (dummy payload):</>');
        $this->line(str_repeat('─', 60));
        $this->line("Template : <fg=white>{$content}</>");

        $dummy  = $this->builder->dummyForEvent($eventKey);
        $output = $this->registry->render($content, $dummy, $eventKey);

        $this->line("Output   : <fg=green>{$output}</>");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function groupEvents(array $events): array
    {
        $groups = [
            'User / Auth'           => ['user_registered', 'password_reset_requested'],
            'Order / Payment'       => ['order_created', 'payment_submitted', 'admin_payment_submitted', 'payment_approved', 'payment_rejected', 'order_expired', 'payment_reminder', 'admin_order_created'],
            'Access'                => ['access_granted'],
            'Event'                 => ['event_registration_confirmed', 'admin_event_registration', 'event_reminder_day_before', 'event_reminder_hour_before'],
            'Course'                => ['course_enrolled'],
            'Affiliate / Payout'    => ['affiliate_commission_created', 'commission_payout_paid', 'admin_payout_paid', 'admin_commission_payout_paid'],
            'Subscriber Automation' => ['order_paid_customer', 'epi_channel_active', 'event_registration', 'course_enrollment', 'subscriber_automation'],
            'Test'                  => ['test_whatsapp', 'test_whatsapp_cli', 'test_email', 'test_email_cli'],
        ];

        // Pastikan event yang belum dikelompokkan masuk ke "Lainnya"
        $listed = array_merge(...array_values($groups));
        $others = array_values(array_diff($events, $listed));
        if ($others !== []) {
            $groups['Lainnya'] = $others;
        }

        // Hanya tampilkan group yang memiliki event valid
        foreach ($groups as $group => $keys) {
            $groups[$group] = array_values(array_intersect($keys, $events));
            if ($groups[$group] === []) {
                unset($groups[$group]);
            }
        }

        return $groups;
    }
}
