<?php

namespace App\Services\Notifications;

class NotificationShortcodeRegistry
{
    /**
     * Alias lama => canonical key.
     * Hanya untuk backward compatibility — jangan tampilkan di UI sebagai pilihan utama.
     */
    private const ALIASES = [
        'name'   => 'member_name',
        'amount' => null, // context-aware, resolved in normalizeAlias()
        'status' => null, // context-aware
    ];

    /**
     * Alias amount/status yang context-aware per event.
     */
    private const AMOUNT_ALIAS_MAP = [
        'payment_submitted'       => 'payment_amount',
        'admin_payment_submitted' => 'payment_amount',
        'commission_payout_paid'  => 'payout_amount',
        'admin_payout_paid'       => 'payout_amount',
        'admin_commission_payout_paid' => 'payout_amount',
    ];

    private const STATUS_ALIAS_MAP = [
        'affiliate_commission_created' => 'commission_status',
    ];

    /**
     * Alias tambahan untuk menjaga template lama yang pernah memakai key
     * canonical event lain pada konteks yang berbeda.
     */
    private const LEGACY_EVENT_ALIAS_MAP = [
        'order_created' => [
            'payment_amount' => 'total_amount',
        ],
        'admin_order_created' => [
            'payment_amount' => 'total_amount',
        ],
    ];

    /**
     * Mapping event => daftar canonical shortcode key yang relevan.
     */
    private const EVENT_SHORTCODES = [
        'user_registered' => [
            'member_name', 'member_email', 'member_whatsapp',
            'dashboard_url', 'login_url', 'sent_at',
        ],
        'password_reset_requested' => [
            'member_name', 'member_email', 'login_url', 'reset_url', 'sent_at',
        ],
        'order_created' => [
            'member_name', 'member_email', 'member_whatsapp',
            'order_number', 'products_list', 'product_name',
            'total_amount', 'payment_url', 'dashboard_url',
        ],
        'payment_submitted' => [
            'member_name', 'payment_number', 'order_number',
            'payment_amount', 'total_amount', 'sent_at',
        ],
        'admin_payment_submitted' => [
            'member_name', 'member_email', 'member_whatsapp',
            'payment_number', 'order_number', 'payment_amount',
            'admin_payment_url',
        ],
        'payment_approved' => [
            'member_name', 'order_number', 'payment_number',
            'products_list', 'produk_saya_url', 'total_amount',
        ],
        'payment_rejected' => [
            'member_name', 'payment_number', 'order_number',
            'reason', 'payment_url',
        ],
        'order_expired' => [
            'member_name', 'order_number', 'products_list',
            'total_amount', 'payment_url',
        ],
        'access_granted' => [
            'member_name', 'product_name', 'produk_saya_url',
        ],
        'event_registration_confirmed' => [
            'member_name', 'event_name', 'event_datetime',
            'event_schedule', 'event_location', 'event_url',
        ],
        'admin_event_registration' => [
            'member_name', 'member_email', 'member_whatsapp',
            'event_name', 'event_datetime', 'admin_event_registration_url',
        ],
        'course_enrolled' => [
            'member_name', 'course_name', 'course_description', 'course_url',
        ],
        'affiliate_commission_created' => [
            'member_name', 'product_name', 'commission_amount',
            'commission_status', 'dashboard_url', 'epic_code',
        ],
        'commission_payout_paid' => [
            'member_name', 'payout_amount', 'paid_at', 'dashboard_url',
        ],
        'admin_payout_paid' => [
            'member_name', 'member_email', 'payout_amount',
            'paid_at', 'admin_payout_url',
        ],
        'admin_commission_payout_paid' => [
            'member_name', 'member_email', 'payout_amount',
            'paid_at', 'admin_payout_url',
        ],
        'admin_order_created' => [
            'member_name', 'member_email', 'member_whatsapp',
            'order_number', 'products_list', 'total_amount', 'admin_order_url',
        ],
        'payment_reminder' => [
            'member_name', 'order_number', 'products_list',
            'total_amount', 'payment_url',
        ],
        'event_reminder_day_before' => [
            'member_name', 'event_name', 'event_datetime',
            'event_location', 'event_url',
        ],
        'event_reminder_hour_before' => [
            'member_name', 'event_name', 'event_datetime',
            'event_location', 'event_url',
        ],
        'test_whatsapp'     => ['member_name', 'sent_at'],
        'test_whatsapp_cli' => ['member_name', 'sent_at'],
        'test_email'        => ['member_name', 'sent_at'],
        'test_email_cli'    => ['member_name', 'sent_at'],
        'order_paid_customer'     => ['member_name', 'member_email', 'order_number', 'products_list', 'total_amount', 'produk_saya_url'],
        'epi_channel_active'      => ['member_name', 'member_email', 'dashboard_url', 'epic_code'],
        'event_registration'      => ['member_name', 'member_email', 'event_name', 'event_datetime', 'event_url'],
        'course_enrollment'       => ['member_name', 'member_email', 'course_name', 'course_url'],
        'subscriber_automation'   => ['member_name', 'member_email', 'dashboard_url'],
    ];

    /**
     * Target canonical.
     */
    private const TARGET_ALIASES = [
        'user'            => 'member',
        'affiliate'       => 'sponsor',
        'epi_channel'     => 'sponsor',
        'admin_platform'  => 'admin',
    ];

    /**
     * Beberapa tab UI memakai event dasar, tetapi target tertentu sebenarnya
     * dikirim melalui event admin khusus di runtime.
     */
    private const TARGET_EVENT_OVERRIDES = [
        'order_created' => [
            'admin' => 'admin_order_created',
        ],
        'payment_submitted' => [
            'admin' => 'admin_payment_submitted',
        ],
        'event_registration_confirmed' => [
            'admin' => 'admin_event_registration',
        ],
        'commission_payout_paid' => [
            'admin' => 'admin_payout_paid',
        ],
    ];

    // ── Public API ───────────────────────────────────────────────────────────

    /** Semua canonical shortcode beserta metadata-nya. */
    public function all(): array
    {
        return $this->definitions();
    }

    /** Shortcode yang relevan untuk sebuah event. */
    public function forEvent(string $eventKey): array
    {
        $keys = self::EVENT_SHORTCODES[$eventKey] ?? [];
        $defs = $this->definitions();

        return array_values(array_filter(
            array_map(fn (string $k) => $defs[$k] ?? null, $keys),
            fn ($v) => $v !== null,
        ));
    }

    /** Shortcode yang relevan untuk event + target tertentu. */
    public function forTarget(string $eventKey, string $targetKey): array
    {
        $canonical = $this->normalizeTarget($targetKey);
        $resolvedEventKey = $this->resolveEventKeyForTarget($eventKey, $canonical);

        return array_values(array_filter(
            $this->forEvent($resolvedEventKey),
            fn (array $def) => in_array($canonical, $def['targets'], true),
        ));
    }

    /** Daftar event yang didukung. */
    public function events(): array
    {
        return array_keys(self::EVENT_SHORTCODES);
    }

    /** Deskripsi singkat semua shortcode (key => description). */
    public function descriptions(): array
    {
        return array_map(fn (array $d) => $d['description'], $this->definitions());
    }

    /** Daftar alias lama beserta canonical target-nya. */
    public function aliases(): array
    {
        return [
            'name'   => 'member_name',
            'amount' => 'payment_amount / payout_amount / total_amount (context-aware)',
            'status' => 'commission_status (context-aware)',
        ];
    }

    /** Normalisasi alias target. */
    public function normalizeTarget(string $targetKey): string
    {
        return self::TARGET_ALIASES[$targetKey] ?? $targetKey;
    }

    /** Ekstrak semua pola {shortcode} dari string konten. */
    public function extractShortcodes(string $content): array
    {
        preg_match_all('/\{([a-z0-9_]+)\}/i', $content, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Validasi konten terhadap shortcode yang tersedia untuk sebuah event.
     *
     * @return array{valid: bool, found_shortcodes: string[], invalid_shortcodes: string[], deprecated_aliases: array<string,string>, suggestions: array<string,string>}
     */
    public function validateContent(string $content, string $eventKey, ?string $targetKey = null): array
    {
        $found       = $this->extractShortcodes($content);
        $resolvedEventKey = $targetKey !== null
            ? $this->resolveEventKeyForTarget($eventKey, $this->normalizeTarget($targetKey))
            : $eventKey;
        $validKeys   = array_column(
            $targetKey !== null
                ? $this->forTarget($eventKey, $targetKey)
                : $this->forEvent($resolvedEventKey),
            'key'
        );
        $allKeys     = array_keys($this->definitions());
        $aliasKeys   = array_unique(array_merge(
            array_keys(self::ALIASES),
            array_keys(self::LEGACY_EVENT_ALIAS_MAP[$resolvedEventKey] ?? []),
        ));

        $invalid    = [];
        $deprecated = [];
        $suggestions = [];

        foreach ($found as $key) {
            if (in_array($key, $validKeys, true)) {
                continue;
            }

            if (in_array($key, $aliasKeys, true)) {
                $canonical = $this->resolveAlias($key, $resolvedEventKey);
                $deprecated[$key] = $canonical;
                continue;
            }

            $invalid[] = $key;

            $suggestion = $this->suggest($key, $allKeys);
            if ($suggestion !== null) {
                $suggestions[$key] = $suggestion;
            }
        }

        return [
            'valid'              => $invalid === [],
            'found_shortcodes'   => $found,
            'invalid_shortcodes' => $invalid,
            'deprecated_aliases' => $deprecated,
            'suggestions'        => $suggestions,
        ];
    }

    /**
     * Render shortcode dalam konten dengan payload.
     * - Mendukung canonical + alias lama.
     * - Fallback "-" untuk nilai kosong.
     * - Tidak menyentuh {{double_brace}} milik Landing Page ZIP.
     */
    public function render(string $content, array $payload, ?string $eventKey = null): string
    {
        // Tambahkan alias key ke payload agar alias lama tetap bekerja.
        $payload = $this->expandAliases($payload, $eventKey);

        $result = preg_replace_callback('/\{([a-z0-9_]+)\}/i', function (array $m) use ($payload): string {
            $key   = strtolower($m[1]);
            $value = $payload[$key] ?? null;

            if ($value === null || $value === '') {
                // URL shortcode: biarkan kosong agar tidak merusak link
                if (str_ends_with($key, '_url')) {
                    return '';
                }
                return '-';
            }

            return (string) $value;
        }, $content);

        return $result ?? $content;
    }

    public function normalizeContent(string $content, string $eventKey, ?string $targetKey = null): string
    {
        if ($content === '') {
            return '';
        }

        $resolvedEventKey = $targetKey !== null
            ? $this->resolveEventKeyForTarget($eventKey, $this->normalizeTarget($targetKey))
            : $eventKey;
        $aliasMap = $this->canonicalAliasMap($resolvedEventKey);

        if ($aliasMap === []) {
            return $content;
        }

        $result = preg_replace_callback('/\{([a-z0-9_]+)\}/i', function (array $matches) use ($aliasMap): string {
            $key = strtolower($matches[1]);
            $canonical = $aliasMap[$key] ?? null;

            return $canonical !== null ? '{'.$canonical.'}' : $matches[0];
        }, $content);

        return $result ?? $content;
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function resolveAlias(string $alias, string $eventKey): string
    {
        if ($alias === 'amount') {
            return self::AMOUNT_ALIAS_MAP[$eventKey] ?? 'total_amount';
        }

        if ($alias === 'status') {
            return self::STATUS_ALIAS_MAP[$eventKey] ?? 'status';
        }

        if (isset(self::LEGACY_EVENT_ALIAS_MAP[$eventKey][$alias])) {
            return self::LEGACY_EVENT_ALIAS_MAP[$eventKey][$alias];
        }

        return self::ALIASES[$alias] ?? $alias;
    }

    /** @return array<string, string> */
    private function canonicalAliasMap(string $eventKey): array
    {
        $aliases = [];

        foreach (array_keys(self::ALIASES) as $alias) {
            $canonical = $this->resolveAlias($alias, $eventKey);

            if ($canonical !== $alias) {
                $aliases[$alias] = $canonical;
            }
        }

        foreach (self::LEGACY_EVENT_ALIAS_MAP[$eventKey] ?? [] as $alias => $canonical) {
            if ($canonical !== $alias) {
                $aliases[$alias] = $canonical;
            }
        }

        return $aliases;
    }

    public function resolveEventKeyForTarget(string $eventKey, string $targetKey): string
    {
        $canonicalTarget = $this->normalizeTarget($targetKey);

        return self::TARGET_EVENT_OVERRIDES[$eventKey][$canonicalTarget] ?? $eventKey;
    }

    public function resolveTemplateEventKey(string $eventKey, string $targetKey): string
    {
        $canonicalTarget = $this->normalizeTarget($targetKey);

        if ($canonicalTarget !== 'admin') {
            return $eventKey;
        }

        foreach (self::TARGET_EVENT_OVERRIDES as $baseEventKey => $targets) {
            if (($targets[$canonicalTarget] ?? null) === $eventKey) {
                return $baseEventKey;
            }
        }

        return match ($eventKey) {
            'admin_commission_payout_paid' => 'commission_payout_paid',
            default => $eventKey,
        };
    }

    /** Tambahkan key alias ke payload supaya render berjalan untuk template lama. */
    private function expandAliases(array $payload, ?string $eventKey): array
    {
        // name => member_name
        if (! isset($payload['name']) && isset($payload['member_name'])) {
            $payload['name'] = $payload['member_name'];
        }

        // member_name => name
        if (! isset($payload['member_name']) && isset($payload['name'])) {
            $payload['member_name'] = $payload['name'];
        }

        // amount (context-aware)
        if (! isset($payload['amount'])) {
            $canonical = self::AMOUNT_ALIAS_MAP[$eventKey ?? ''] ?? null;
            if ($canonical && isset($payload[$canonical])) {
                $payload['amount'] = $payload[$canonical];
            } elseif (isset($payload['total_amount'])) {
                $payload['amount'] = $payload['total_amount'];
            }
        }

        // status (context-aware)
        if (! isset($payload['status'])) {
            $canonical = self::STATUS_ALIAS_MAP[$eventKey ?? ''] ?? null;
            if ($canonical && isset($payload[$canonical])) {
                $payload['status'] = $payload[$canonical];
            }
        }

        foreach (self::LEGACY_EVENT_ALIAS_MAP[$eventKey ?? ''] ?? [] as $legacyAlias => $canonical) {
            if (! isset($payload[$legacyAlias]) && isset($payload[$canonical])) {
                $payload[$legacyAlias] = $payload[$canonical];
            }
        }

        return $payload;
    }

    /** Cari shortcode mirip menggunakan similar_text. */
    private function suggest(string $input, array $candidates): ?string
    {
        $best  = null;
        $score = 0;

        foreach ($candidates as $candidate) {
            similar_text($input, $candidate, $pct);
            if ($candidate !== $input && $pct > $score && $pct >= 50) {
                $score = $pct;
                $best  = $candidate;
            }
        }

        return $best;
    }

    /** Definisi lengkap semua canonical shortcode. */
    private function definitions(): array
    {
        $all = [
            // ── Identity / Member ────────────────────────────────────────────
            'member_name' => [
                'key'              => 'member_name',
                'shortcode'        => '{member_name}',
                'label'            => 'Nama Member',
                'description'      => 'Nama pengguna/member penerima notifikasi.',
                'example'          => 'Budi Santoso',
                'events'           => ['user_registered','password_reset_requested','order_created','payment_submitted','admin_payment_submitted','payment_approved','payment_rejected','order_expired','access_granted','event_registration_confirmed','admin_event_registration','course_enrolled','affiliate_commission_created','commission_payout_paid','admin_payout_paid','admin_commission_payout_paid','admin_order_created','payment_reminder','event_reminder_day_before','event_reminder_hour_before','test_whatsapp','test_whatsapp_cli','test_email','test_email_cli','order_paid_customer','epi_channel_active','event_registration','course_enrollment','subscriber_automation'],
                'targets'          => ['member','sponsor','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'member_email' => [
                'key'              => 'member_email',
                'shortcode'        => '{member_email}',
                'label'            => 'Email Member',
                'description'      => 'Alamat email member.',
                'example'          => 'budi@example.com',
                'events'           => ['user_registered','password_reset_requested','order_created','admin_payment_submitted','admin_event_registration','admin_order_created','admin_payout_paid','admin_commission_payout_paid','order_paid_customer','epi_channel_active','event_registration','course_enrollment','subscriber_automation'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> false,
            ],
            'member_whatsapp' => [
                'key'              => 'member_whatsapp',
                'shortcode'        => '{member_whatsapp}',
                'label'            => 'WhatsApp Member',
                'description'      => 'Nomor WhatsApp member.',
                'example'          => '628123456789',
                'events'           => ['user_registered','order_created','admin_payment_submitted','admin_event_registration','admin_order_created'],
                'targets'          => ['admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> false,
            ],
            'epic_code' => [
                'key'              => 'epic_code',
                'shortcode'        => '{epic_code}',
                'label'            => 'Kode EPI',
                'description'      => 'Kode referral EPI Channel milik member.',
                'example'          => 'EPI-ABCD',
                'events'           => ['affiliate_commission_created','epi_channel_active'],
                'targets'          => ['sponsor'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── Auth / Dashboard ─────────────────────────────────────────────
            'dashboard_url' => [
                'key'              => 'dashboard_url',
                'shortcode'        => '{dashboard_url}',
                'label'            => 'URL Dashboard',
                'description'      => 'URL halaman dashboard member.',
                'example'          => 'https://epichub.id/dashboard',
                'events'           => ['user_registered','order_created','affiliate_commission_created','commission_payout_paid','epi_channel_active','subscriber_automation'],
                'targets'          => ['member','sponsor'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'login_url' => [
                'key'              => 'login_url',
                'shortcode'        => '{login_url}',
                'label'            => 'URL Login',
                'description'      => 'URL halaman login.',
                'example'          => 'https://epichub.id/login',
                'events'           => ['user_registered','password_reset_requested'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── Product / Order / Payment ────────────────────────────────────
            'product_name' => [
                'key'              => 'product_name',
                'shortcode'        => '{product_name}',
                'label'            => 'Nama Produk',
                'description'      => 'Nama produk utama dalam order/akses.',
                'example'          => 'Kelas Digital Marketing',
                'events'           => ['order_created','access_granted','affiliate_commission_created'],
                'targets'          => ['member','sponsor'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'products_list' => [
                'key'              => 'products_list',
                'shortcode'        => '{products_list}',
                'label'            => 'Daftar Produk',
                'description'      => 'Daftar semua produk dalam order (dipisah newline atau koma).',
                'example'          => "Kelas Digital Marketing\nE-Book SEO",
                'events'           => ['order_created','payment_approved','order_expired','admin_order_created','payment_reminder','order_paid_customer'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'order_number' => [
                'key'              => 'order_number',
                'shortcode'        => '{order_number}',
                'label'            => 'Nomor Order',
                'description'      => 'Nomor unik order.',
                'example'          => 'ORD-20240501-001',
                'events'           => ['order_created','payment_submitted','admin_payment_submitted','payment_approved','payment_rejected','order_expired','admin_order_created','payment_reminder'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'payment_number' => [
                'key'              => 'payment_number',
                'shortcode'        => '{payment_number}',
                'label'            => 'Nomor Pembayaran',
                'description'      => 'Nomor unik pembayaran.',
                'example'          => 'PAY-20240501-001',
                'events'           => ['payment_submitted','admin_payment_submitted','payment_approved','payment_rejected'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'total_amount' => [
                'key'              => 'total_amount',
                'shortcode'        => '{total_amount}',
                'label'            => 'Total Order',
                'description'      => 'Total nilai order (sudah diformat Rupiah).',
                'example'          => 'Rp 500.000',
                'events'           => ['order_created','payment_submitted','payment_approved','order_expired','admin_order_created','payment_reminder','order_paid_customer'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'payment_amount' => [
                'key'              => 'payment_amount',
                'shortcode'        => '{payment_amount}',
                'label'            => 'Nominal Pembayaran',
                'description'      => 'Nominal yang dibayarkan (sudah diformat Rupiah).',
                'example'          => 'Rp 500.000',
                'events'           => ['payment_submitted','admin_payment_submitted'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'payment_url' => [
                'key'              => 'payment_url',
                'shortcode'        => '{payment_url}',
                'label'            => 'URL Pembayaran',
                'description'      => 'Link halaman upload bukti pembayaran.',
                'example'          => 'https://epichub.id/orders/ORD-001/pay',
                'events'           => ['order_created','payment_rejected','order_expired','payment_reminder'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'produk_saya_url' => [
                'key'              => 'produk_saya_url',
                'shortcode'        => '{produk_saya_url}',
                'label'            => 'URL Produk Saya',
                'description'      => 'Link halaman Produk Saya member.',
                'example'          => 'https://epichub.id/my-products',
                'events'           => ['payment_approved','access_granted','order_paid_customer'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── Event ────────────────────────────────────────────────────────
            'event_name' => [
                'key'              => 'event_name',
                'shortcode'        => '{event_name}',
                'label'            => 'Nama Event',
                'description'      => 'Judul/nama event.',
                'example'          => 'Workshop Digital Marketing 2024',
                'events'           => ['event_registration_confirmed','admin_event_registration','event_reminder_day_before','event_reminder_hour_before','event_registration'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'event_datetime' => [
                'key'              => 'event_datetime',
                'shortcode'        => '{event_datetime}',
                'label'            => 'Tanggal & Waktu Event',
                'description'      => 'Tanggal dan waktu mulai event (terformat).',
                'example'          => '01 Mei 2024, 09:00 WIB',
                'events'           => ['event_registration_confirmed','admin_event_registration','event_reminder_day_before','event_reminder_hour_before','event_registration'],
                'targets'          => ['member','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'event_schedule' => [
                'key'              => 'event_schedule',
                'shortcode'        => '{event_schedule}',
                'label'            => 'Jadwal Event',
                'description'      => 'Jadwal lengkap event (mulai – selesai).',
                'example'          => '01 Mei 2024, 09:00 - 12:00 WIB',
                'events'           => ['event_registration_confirmed'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'event_location' => [
                'key'              => 'event_location',
                'shortcode'        => '{event_location}',
                'label'            => 'Lokasi Event',
                'description'      => 'Lokasi penyelenggaraan event (atau "Online").',
                'example'          => 'Online / Zoom',
                'events'           => ['event_registration_confirmed','event_reminder_day_before','event_reminder_hour_before'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'event_url' => [
                'key'              => 'event_url',
                'shortcode'        => '{event_url}',
                'label'            => 'URL Event',
                'description'      => 'Link halaman detail event member.',
                'example'          => 'https://epichub.id/my-events/123',
                'events'           => ['event_registration_confirmed','event_reminder_day_before','event_reminder_hour_before','event_registration'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── Course ───────────────────────────────────────────────────────
            'course_name' => [
                'key'              => 'course_name',
                'shortcode'        => '{course_name}',
                'label'            => 'Nama Kursus',
                'description'      => 'Judul kursus/course.',
                'example'          => 'Kelas SEO Dasar',
                'events'           => ['course_enrolled','course_enrollment'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'course_description' => [
                'key'              => 'course_description',
                'shortcode'        => '{course_description}',
                'label'            => 'Deskripsi Kursus',
                'description'      => 'Deskripsi singkat kursus.',
                'example'          => 'Belajar SEO dari nol hingga mahir.',
                'events'           => ['course_enrolled'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> false,
            ],
            'course_url' => [
                'key'              => 'course_url',
                'shortcode'        => '{course_url}',
                'label'            => 'URL Kursus',
                'description'      => 'Link halaman kursus member.',
                'example'          => 'https://epichub.id/my-courses/123',
                'events'           => ['course_enrolled','course_enrollment'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── Commission / Payout ──────────────────────────────────────────
            'commission_amount' => [
                'key'              => 'commission_amount',
                'shortcode'        => '{commission_amount}',
                'label'            => 'Nominal Komisi',
                'description'      => 'Nominal komisi affiliate (sudah diformat Rupiah).',
                'example'          => 'Rp 100.000',
                'events'           => ['affiliate_commission_created'],
                'targets'          => ['sponsor'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'commission_status' => [
                'key'              => 'commission_status',
                'shortcode'        => '{commission_status}',
                'label'            => 'Status Komisi',
                'description'      => 'Status komisi affiliate (misal: Pending, Approved).',
                'example'          => 'Pending',
                'events'           => ['affiliate_commission_created'],
                'targets'          => ['sponsor'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'payout_amount' => [
                'key'              => 'payout_amount',
                'shortcode'        => '{payout_amount}',
                'label'            => 'Nominal Payout',
                'description'      => 'Nominal payout komisi (sudah diformat Rupiah).',
                'example'          => 'Rp 500.000',
                'events'           => ['commission_payout_paid','admin_payout_paid','admin_commission_payout_paid'],
                'targets'          => ['sponsor','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'paid_at' => [
                'key'              => 'paid_at',
                'shortcode'        => '{paid_at}',
                'label'            => 'Tanggal Dibayar',
                'description'      => 'Tanggal dan waktu payout diproses.',
                'example'          => '01 Mei 2024, 10:30 WIB',
                'events'           => ['commission_payout_paid','admin_payout_paid','admin_commission_payout_paid'],
                'targets'          => ['sponsor','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── Admin URL ────────────────────────────────────────────────────
            'admin_order_url' => [
                'key'              => 'admin_order_url',
                'shortcode'        => '{admin_order_url}',
                'label'            => 'URL Admin Order',
                'description'      => 'Link halaman order di panel admin.',
                'example'          => 'https://epichub.id/admin/orders/123/edit',
                'events'           => ['admin_order_created'],
                'targets'          => ['admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'admin_payment_url' => [
                'key'              => 'admin_payment_url',
                'shortcode'        => '{admin_payment_url}',
                'label'            => 'URL Admin Payment',
                'description'      => 'Link halaman payment di panel admin.',
                'example'          => 'https://epichub.id/admin/payments/123/edit',
                'events'           => ['admin_payment_submitted'],
                'targets'          => ['admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'admin_event_registration_url' => [
                'key'              => 'admin_event_registration_url',
                'shortcode'        => '{admin_event_registration_url}',
                'label'            => 'URL Admin Event Registration',
                'description'      => 'Link halaman registrasi event di panel admin.',
                'example'          => 'https://epichub.id/admin/event-registrations/123/edit',
                'events'           => ['admin_event_registration'],
                'targets'          => ['admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'admin_payout_url' => [
                'key'              => 'admin_payout_url',
                'shortcode'        => '{admin_payout_url}',
                'label'            => 'URL Admin Payout',
                'description'      => 'Link halaman payout di panel admin.',
                'example'          => 'https://epichub.id/admin/commission-payouts',
                'events'           => ['admin_payout_paid','admin_commission_payout_paid'],
                'targets'          => ['admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],

            // ── General ──────────────────────────────────────────────────────
            'reason' => [
                'key'              => 'reason',
                'shortcode'        => '{reason}',
                'label'            => 'Alasan',
                'description'      => 'Alasan penolakan atau keterangan tambahan.',
                'example'          => 'Bukti pembayaran tidak jelas',
                'events'           => ['payment_rejected'],
                'targets'          => ['member'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'sent_at' => [
                'key'              => 'sent_at',
                'shortcode'        => '{sent_at}',
                'label'            => 'Waktu Terkirim',
                'description'      => 'Waktu notifikasi dikirim.',
                'example'          => '01 Mei 2024, 08:00 WIB',
                'events'           => ['user_registered','password_reset_requested','payment_submitted','test_whatsapp','test_whatsapp_cli','test_email','test_email_cli'],
                'targets'          => ['member','sponsor','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'tanggal' => [
                'key'              => 'tanggal',
                'shortcode'        => '{tanggal}',
                'label'            => 'Tanggal',
                'description'      => 'Tanggal saat ini (format: d M Y).',
                'example'          => '01 Mei 2024',
                'events'           => [],
                'targets'          => ['member','sponsor','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
            'waktu' => [
                'key'              => 'waktu',
                'shortcode'        => '{waktu}',
                'label'            => 'Waktu',
                'description'      => 'Waktu saat ini (format: H:i WIB).',
                'example'          => '08:00 WIB',
                'events'           => [],
                'targets'          => ['member','sponsor','admin'],
                'safe_for_email'   => true,
                'safe_for_whatsapp'=> true,
            ],
        ];

        // Pastikan setiap entry memiliki field 'key'
        foreach ($all as $k => &$def) {
            $def['key'] = $k;
        }

        return $all;
    }
}
