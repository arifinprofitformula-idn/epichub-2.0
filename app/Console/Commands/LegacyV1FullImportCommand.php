<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class LegacyV1FullImportCommand extends Command
{
    protected $signature = 'legacy:v1-full-import
        {--legacy=legacy_mysql : Nama koneksi database EPIC HUB 1.0}
        {--batch=legacy-v1-final : Nama batch migrasi}
        {--stage=all : all|products|users|sponsors|orders|accesses|payments|commissions|payouts|report}
        {--chunk=500 : Jumlah data per batch}
        {--reset-staging : Hapus ulang data staging batch ini sebelum import}
        {--skip-grant : Jangan grant akses ke user_products}
        {--dry-run : Cek koneksi dan estimasi tanpa menulis data}';

    protected $description = 'Full import database EPIC HUB 1.0 ke EPIC HUB 2.0.';

    protected int $batchId;
    protected string $batchName;
    protected string $legacyConnection;
    protected int $chunk;

    public function handle(): int
    {
        $this->legacyConnection = (string) $this->option('legacy');
        $this->batchName = (string) $this->option('batch');
        $this->chunk = (int) $this->option('chunk');

        $stage = (string) $this->option('stage');

        $this->info('EPIC HUB Legacy V1 Full Import');
        $this->line('Batch: ' . $this->batchName);
        $this->line('Legacy connection: ' . $this->legacyConnection);
        $this->line('Stage: ' . $stage);

        if ($this->option('dry-run')) {
            return $this->dryRun();
        }

        $this->batchId = $this->getOrCreateBatch();

        if ($this->option('reset-staging')) {
            $this->resetStaging();
        }

        try {
            if ($stage === 'all' || $stage === 'products') {
                $this->importProducts();
            }

            if ($stage === 'all' || $stage === 'users') {
                $this->stageUsers();
                $this->commitUsers();
            }

            if ($stage === 'all' || $stage === 'sponsors') {
                $this->resolveSponsors();
            }

            if ($stage === 'all' || $stage === 'orders') {
                $this->importOrders();
            }

            if ($stage === 'all' || $stage === 'accesses') {
                $this->importProductAccesses();
            }

            if ($stage === 'all' || $stage === 'payments') {
                $this->importPayments();
            }

            if ($stage === 'all' || $stage === 'commissions') {
                $this->importCommissions();
            }

            if ($stage === 'all' || $stage === 'payouts') {
                $this->importPayouts();
            }

            if ($stage === 'all' || $stage === 'report') {
                $this->report();
            }

            DB::table('legacy_v1_import_batches')
                ->where('id', $this->batchId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->info('Migrasi selesai.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            DB::table('legacy_v1_import_batches')
                ->where('id', $this->batchId)
                ->update([
                    'status' => 'failed',
                    'summary' => json_encode([
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]),
                    'updated_at' => now(),
                ]);

            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function dryRun(): int
    {
        $legacy = DB::connection($this->legacyConnection);

        $this->table(
            ['Data', 'Jumlah'],
            [
                ['sa_member', $legacy->table('sa_member')->count()],
                ['sa_sponsor', $legacy->table('sa_sponsor')->count()],
                ['sa_page', $legacy->table('sa_page')->count()],
                ['sa_order', $legacy->table('sa_order')->count()],
                ['epi_payment_confirm', $legacy->table('epi_payment_confirm')->count()],
                ['sa_laporan', $legacy->table('sa_laporan')->count()],
                ['epi_commission_payout', $legacy->table('epi_commission_payout')->count()],
            ]
        );

        $this->info('Dry-run sukses. Belum ada data ditulis.');
        return self::SUCCESS;
    }

    protected function getOrCreateBatch(): int
    {
        $existing = DB::table('legacy_v1_import_batches')
            ->where('name', $this->batchName)
            ->first();

        if ($existing) {
            DB::table('legacy_v1_import_batches')
                ->where('id', $existing->id)
                ->update([
                    'status' => 'running',
                    'started_at' => $existing->started_at ?: now(),
                    'updated_at' => now(),
                ]);

            return (int) $existing->id;
        }

        return (int) DB::table('legacy_v1_import_batches')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => $this->batchName,
            'source_type' => 'database',
            'status' => 'running',
            'started_at' => now(),
            'metadata' => json_encode([
                'legacy_connection' => $this->legacyConnection,
                'source' => 'EPIC HUB 1.0',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function resetStaging(): void
    {
        $this->warn('Reset staging batch: ' . $this->batchId);

        DB::table('legacy_v1_product_accesses')->where('batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_sponsor_links')->where('batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_user_mappings')->where('batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_orders')->where('batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_payments')->where('batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_payouts')->where('batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_import_errors')->where('batch_id', $this->batchId)->delete();

        DB::table('legacy_v1_commissions')->where('import_batch_id', $this->batchId)->delete();
        DB::table('legacy_v1_users')->where('batch_id', $this->batchId)->delete();
    }

    protected function importProducts(): void
    {
        $this->info('Import products dari sa_page...');

        $legacy = DB::connection($this->legacyConnection);

        $legacy->table('sa_page')
            ->orderBy('page_id')
            ->chunk($this->chunk, function ($pages) {
                foreach ($pages as $page) {
                    $slug = Str::slug($page->page_url ?: $page->page_judul);
                    $type = $this->guessProductType($page->page_url, $page->page_judul);

                    $product = DB::table('products')
                        ->where('slug', $slug)
                        ->first();

                    $payload = [
                        'title' => $page->page_judul,
                        'slug' => $slug,
                        'short_description' => $page->page_diskripsi,
                        'full_description' => $page->page_diskripsi,
                        'product_type' => $type,
                        'price' => (float) ($page->pro_harga ?? 0),
                        'sale_price' => $page->pro_harga_display ? (float) $page->pro_harga_display : null,
                        'status' => (string) $page->pro_status === '1' ? 'published' : 'draft',
                        'visibility' => 'public',
                        'access_type' => 'instant_access',
                        'is_affiliate_enabled' => $page->pro_komisi ? 1 : 0,
                        'metadata' => json_encode([
                            'legacy_source' => 'sa_page',
                            'legacy_page_id' => $page->page_id,
                            'legacy_page_url' => $page->page_url,
                            'legacy_file' => $page->pro_file,
                            'legacy_image' => $page->pro_img,
                            'legacy_commission_raw' => $page->pro_komisi,
                        ]),
                        'updated_at' => now(),
                    ];

                    if ($product) {
                        DB::table('products')->where('id', $product->id)->update($payload);
                        $productId = $product->id;
                    } else {
                        $payload['created_at'] = now();
                        $productId = DB::table('products')->insertGetId($payload);
                    }

                    DB::table('legacy_v1_product_mappings')->updateOrInsert(
                        ['legacy_product_key' => (string) $page->page_id],
                        [
                            'legacy_product_name' => $page->page_judul,
                            'product_id' => $productId,
                            'is_active' => 1,
                            'mapped_at' => now(),
                            'notes' => 'Auto mapped from sa_page.page_id',
                            'metadata' => json_encode([
                                'legacy_slug' => $page->page_url,
                                'legacy_price' => $page->pro_harga,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Import products selesai.');
    }

    protected function stageUsers(): void
    {
        $this->info('Stage users dari sa_member...');

        $legacy = DB::connection($this->legacyConnection);
        $row = 0;

        $legacy->table('sa_member')
            ->orderBy('mem_id')
            ->chunk($this->chunk, function ($members) use (&$row) {
                foreach ($members as $member) {
                    $row++;

                    $rawEpicId = $this->extractLegacyField($member->mem_datalain, 'idepicresmi');
                    $normalizedEpicId = $this->normalizeEpicId($rawEpicId);
                    $normalizedEmail = $this->normalizeEmail($member->mem_email);
                    $normalizedWhatsapp = $this->normalizeWhatsapp($member->mem_whatsapp);

                    DB::table('legacy_v1_users')->updateOrInsert(
                        ['import_key' => 'v1-user-' . $member->mem_id],
                        [
                            'batch_id' => $this->batchId,
                            'row_number' => $row,
                            'legacy_user_id' => (string) $member->mem_id,
                            'source_type' => 'database',
                            'status' => 'staged',
                            'match_status' => 'pending',
                            'sponsor_status' => 'pending',
                            'raw_name' => $member->mem_nama,
                            'raw_epic_id' => $rawEpicId,
                            'raw_email' => $member->mem_email,
                            'raw_whatsapp' => $member->mem_whatsapp,
                            'raw_sponsor_epic_id' => null,
                            'raw_city' => null,
                            'normalized_name' => trim((string) $member->mem_nama),
                            'normalized_epic_id' => $normalizedEpicId,
                            'normalized_email' => $normalizedEmail,
                            'normalized_whatsapp' => $normalizedWhatsapp,
                            'normalized_sponsor_epic_id' => null,
                            'normalized_city' => null,
                            'metadata' => json_encode([
                                'mem_kodeaff' => $member->mem_kodeaff,
                                'mem_status' => $member->mem_status,
                                'mem_role' => $member->mem_role,
                                'mem_tgldaftar' => $member->mem_tgldaftar,
                                'mem_tglupgrade' => $member->mem_tglupgrade,
                                'gender' => $this->extractLegacyField($member->mem_datalain, 'gender'),
                                'grupwa' => $this->extractLegacyField($member->mem_datalain, 'grupwa'),
                                'rekening_exists' => (bool) $this->extractLegacyField($member->mem_datalain, 'rekening'),
                                'fotoprofil' => $this->extractLegacyField($member->mem_datalain, 'fotoprofil'),
                                'fotoktp_exists' => (bool) $this->extractLegacyField($member->mem_datalain, 'fotoktp'),
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Stage users selesai.');
    }

    protected function commitUsers(): void
    {
        $this->info('Commit users ke tabel users + epi_channels...');

        $customerRoleId = DB::table('roles')->where('name', 'customer')->value('id');

        DB::table('legacy_v1_users')
            ->where('batch_id', $this->batchId)
            ->orderBy('id')
            ->chunk($this->chunk, function ($legacyUsers) use ($customerRoleId) {
                foreach ($legacyUsers as $legacyUser) {
                    $email = $legacyUser->normalized_email;

                    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $email = 'legacy-' . $legacyUser->legacy_user_id . '@legacy.epichub.local';

                        $this->logError('user', 'placeholder_email', 'Email kosong/tidak valid. Dibuat placeholder.', [
                            'legacy_user_id' => $legacyUser->legacy_user_id,
                            'raw_email' => $legacyUser->raw_email,
                            'placeholder_email' => $email,
                        ]);
                    }

                    $legacyEpicId = $legacyUser->normalized_epic_id;

                    if ($legacyEpicId) {
                        $duplicateEpicUser = DB::table('users')
                            ->where('legacy_epic_id', $legacyEpicId)
                            ->first();

                        if ($duplicateEpicUser && (string) $duplicateEpicUser->legacy_user_id !== (string) $legacyUser->legacy_user_id) {
                            $this->logError('user', 'duplicate_legacy_epic_id', 'Legacy EPIC ID duplicate. Tidak disimpan ke users.legacy_epic_id.', [
                                'legacy_user_id' => $legacyUser->legacy_user_id,
                                'legacy_epic_id' => $legacyEpicId,
                                'existing_user_id' => $duplicateEpicUser->id,
                            ]);

                            $legacyEpicId = null;
                        }
                    }

                    $existing = DB::table('users')
                        ->where('legacy_source', 'epic_hub_1')
                        ->where('legacy_user_id', $legacyUser->legacy_user_id)
                        ->first();

                    if (!$existing && $legacyEpicId) {
                        $existing = DB::table('users')
                            ->where('legacy_epic_id', $legacyEpicId)
                            ->first();
                    }

                    if (!$existing) {
                        $existing = DB::table('users')
                            ->where('email', $email)
                            ->first();
                    }

                    $payload = [
                        'name' => $legacyUser->normalized_name ?: ('Legacy User ' . $legacyUser->legacy_user_id),
                        'email' => $email,
                        'legacy_epic_id' => $legacyEpicId,
                        'legacy_source' => 'epic_hub_1',
                        'legacy_user_id' => $legacyUser->legacy_user_id,
                        'legacy_import_batch_id' => $this->batchId,
                        'legacy_imported_at' => now(),
                        'must_reset_password' => 1,
                        'whatsapp_number' => $legacyUser->normalized_whatsapp,
                        'updated_at' => now(),
                    ];

                    if ($existing) {
                        DB::table('users')->where('id', $existing->id)->update($payload);
                        $userId = $existing->id;
                    } else {
                        $payload['password'] = Hash::make(Str::random(48));
                        $payload['created_at'] = now();
                        $userId = DB::table('users')->insertGetId($payload);
                    }

                    if ($customerRoleId) {
                        DB::table('model_has_roles')->updateOrInsert(
                            [
                                'role_id' => $customerRoleId,
                                'model_type' => 'App\\Models\\User',
                                'model_id' => $userId,
                            ],
                            []
                        );
                    }

                    $epiChannelId = $this->ensureEpiChannel($userId, $legacyUser);

                    DB::table('legacy_v1_users')
                        ->where('id', $legacyUser->id)
                        ->update([
                            'matched_user_id' => $userId,
                            'matched_by' => $existing ? 'existing' : 'created_new',
                            'imported_user_id' => $userId,
                            'epi_channel_id' => $epiChannelId,
                            'status' => 'imported',
                            'match_status' => 'matched',
                            'imported_at' => now(),
                            'updated_at' => now(),
                        ]);

                    DB::table('legacy_v1_user_mappings')->updateOrInsert(
                        [
                            'batch_id' => $this->batchId,
                            'legacy_user_id' => $legacyUser->legacy_user_id,
                        ],
                        [
                            'legacy_v1_user_id' => $legacyUser->id,
                            'legacy_epic_id' => $legacyEpicId,
                            'legacy_email' => $email,
                            'legacy_whatsapp' => $legacyUser->normalized_whatsapp,
                            'user_id' => $userId,
                            'match_method' => $existing ? 'existing' : 'created_new',
                            'status' => 'mapped',
                            'metadata' => json_encode([
                                'epi_channel_id' => $epiChannelId,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Commit users selesai.');
    }

    protected function resolveSponsors(): void
    {
        $this->info('Resolve sponsor/referrer...');

        $houseChannelId = DB::table('epi_channels')
            ->where('epic_code', 'EPIC-HOUSE')
            ->value('id');

        if (!$houseChannelId) {
            throw new \RuntimeException('EPIC-HOUSE tidak ditemukan di tabel epi_channels.');
        }

        $legacy = DB::connection($this->legacyConnection);

        DB::table('legacy_v1_users')
            ->where('batch_id', $this->batchId)
            ->whereNotNull('matched_user_id')
            ->orderBy('id')
            ->chunk($this->chunk, function ($legacyUsers) use ($legacy, $houseChannelId) {
                foreach ($legacyUsers as $legacyUser) {
                    $sponsorRow = $legacy->table('sa_sponsor')
                        ->where('sp_mem_id', $legacyUser->legacy_user_id)
                        ->first();

                    $sponsorLegacyUserId = $sponsorRow?->sp_sponsor_id;

                    $resolvedChannelId = $houseChannelId;
                    $resolvedSponsorUserId = null;
                    $reason = 'fallback_house_channel';

                    if ($sponsorLegacyUserId && (string) $sponsorLegacyUserId !== '0') {
                        $sponsorLegacyUser = DB::table('legacy_v1_users')
                            ->where('batch_id', $this->batchId)
                            ->where('legacy_user_id', (string) $sponsorLegacyUserId)
                            ->first();

                        if ($sponsorLegacyUser && $sponsorLegacyUser->epi_channel_id && (int) $sponsorLegacyUser->matched_user_id !== (int) $legacyUser->matched_user_id) {
                            $resolvedChannelId = $sponsorLegacyUser->epi_channel_id;
                            $resolvedSponsorUserId = $sponsorLegacyUser->matched_user_id;
                            $reason = 'resolved_from_legacy_sponsor';
                        } else {
                            $this->logError('sponsor', 'unresolved_sponsor', 'Sponsor tidak ditemukan/self-referral. Fallback ke EPIC-HOUSE.', [
                                'legacy_user_id' => $legacyUser->legacy_user_id,
                                'sponsor_legacy_user_id' => $sponsorLegacyUserId,
                            ]);
                        }
                    }

                    $user = DB::table('users')->where('id', $legacyUser->matched_user_id)->first();

                    DB::table('legacy_v1_sponsor_links')->updateOrInsert(
                        ['legacy_v1_user_id' => $legacyUser->id],
                        [
                            'batch_id' => $this->batchId,
                            'user_id' => $legacyUser->matched_user_id,
                            'sponsor_legacy_epic_id' => $sponsorLegacyUserId ? (string) $sponsorLegacyUserId : null,
                            'previous_referrer_epi_channel_id' => $user?->referrer_epi_channel_id,
                            'resolved_sponsor_user_id' => $resolvedSponsorUserId,
                            'resolved_referrer_epi_channel_id' => $resolvedChannelId,
                            'resolution_status' => 'applied',
                            'forced' => 0,
                            'resolution_reason' => $reason,
                            'applied_at' => now(),
                            'metadata' => json_encode([
                                'sp_mem_id' => $legacyUser->legacy_user_id,
                                'sp_sponsor_id' => $sponsorLegacyUserId,
                                'sp_network' => $sponsorRow?->sp_network,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    if (!$user?->referrer_epi_channel_id) {
                        DB::table('users')->where('id', $legacyUser->matched_user_id)->update([
                            'referrer_epi_channel_id' => $resolvedChannelId,
                            'referral_locked_at' => now(),
                            'referral_source' => 'legacy_import',
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('legacy_v1_users')->where('id', $legacyUser->id)->update([
                        'sponsor_status' => 'resolved',
                        'updated_at' => now(),
                    ]);
                }
            });

        $this->info('Resolve sponsor selesai.');
    }

    protected function importOrders(): void
    {
        $this->info('Import legacy orders...');

        $legacy = DB::connection($this->legacyConnection);

        $legacy->table('sa_order as o')
            ->leftJoin('sa_member as m', 'm.mem_id', '=', 'o.order_idmember')
            ->select('o.*', 'm.mem_nama', 'm.mem_email', 'm.mem_whatsapp')
            ->orderBy('o.order_id')
            ->chunk($this->chunk, function ($orders) {
                foreach ($orders as $order) {
                    $legacyUser = DB::table('legacy_v1_users')
                        ->where('batch_id', $this->batchId)
                        ->where('legacy_user_id', (string) $order->order_idmember)
                        ->first();

                    DB::table('legacy_v1_orders')->updateOrInsert(
                        ['import_key' => 'v1-order-' . $order->order_id],
                        [
                            'batch_id' => $this->batchId,
                            'legacy_order_id' => (string) $order->order_id,
                            'legacy_order_number' => (string) $order->order_id,
                            'legacy_user_id' => (string) $order->order_idmember,
                            'legacy_user_epic_id' => $legacyUser?->normalized_epic_id,
                            'legacy_customer_name' => $order->mem_nama,
                            'legacy_customer_email' => $this->normalizeEmail($order->mem_email),
                            'legacy_customer_whatsapp' => $this->normalizeWhatsapp($order->mem_whatsapp),
                            'user_id' => $legacyUser?->matched_user_id,
                            'legacy_status' => $order->order_status,
                            'normalized_status' => $this->normalizeOrderStatus($order->order_status),
                            'currency' => 'IDR',
                            'subtotal_amount' => (float) $order->order_harga,
                            'discount_amount' => (float) ($order->order_discount ?? 0),
                            'total_amount' => (float) ($order->order_hargaunik ?: $order->order_harga),
                            'ordered_at' => $this->dateOrNull($order->order_tglorder),
                            'paid_at' => $this->dateOrNull($order->order_tglbayar),
                            'migration_status' => $legacyUser?->matched_user_id ? 'resolved' : 'unresolved_user',
                            'raw_payload' => json_encode([
                                'order_idproduk' => $order->order_idproduk,
                                'order_idsponsor' => $order->order_idsponsor,
                                'order_trx' => $order->order_trx,
                                'order_promo_code' => $order->order_promo_code,
                                'order_bukti' => $order->order_bukti,
                                'order_cancel_reason' => $order->order_cancel_reason,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Import legacy orders selesai.');
    }

    protected function importProductAccesses(): void
    {
        $this->info('Import product accesses dari paid order...');

        $legacy = DB::connection($this->legacyConnection);
        $row = 0;

        $legacy->table('sa_order as o')
            ->leftJoin('sa_page as p', 'p.page_id', '=', 'o.order_idproduk')
            ->where('o.order_status', '1')
            ->select('o.*', 'p.page_judul')
            ->orderBy('o.order_id')
            ->chunk($this->chunk, function ($orders) use (&$row) {
                foreach ($orders as $order) {
                    $row++;

                    $legacyUser = DB::table('legacy_v1_users')
                        ->where('batch_id', $this->batchId)
                        ->where('legacy_user_id', (string) $order->order_idmember)
                        ->first();

                    $mapping = DB::table('legacy_v1_product_mappings')
                        ->where('legacy_product_key', (string) $order->order_idproduk)
                        ->where('is_active', 1)
                        ->first();

                    $status = 'resolved';

                    if (!$legacyUser?->matched_user_id) {
                        $status = 'unresolved_user';
                    }

                    if (!$mapping?->product_id) {
                        $status = 'unmapped_product';
                    }

                    DB::table('legacy_v1_product_accesses')->updateOrInsert(
                        ['import_key' => 'v1-access-order-' . $order->order_id],
                        [
                            'batch_id' => $this->batchId,
                            'legacy_v1_user_id' => $legacyUser?->id,
                            'legacy_access_id' => (string) $order->order_id,
                            'source_type' => 'sa_order',
                            'row_number' => $row,
                            'status' => $status,
                            'raw_identifier_type' => 'legacy_user_id',
                            'raw_identifier_value' => (string) $order->order_idmember,
                            'raw_legacy_product_key' => (string) $order->order_idproduk,
                            'raw_legacy_product_name' => $order->page_judul,
                            'raw_granted_at' => $order->order_tglbayar ?: $order->order_tglorder,
                            'normalized_email' => $legacyUser?->normalized_email,
                            'normalized_epic_id' => $legacyUser?->normalized_epic_id,
                            'normalized_whatsapp' => $legacyUser?->normalized_whatsapp,
                            'normalized_legacy_product_key' => (string) $order->order_idproduk,
                            'matched_user_id' => $legacyUser?->matched_user_id,
                            'matched_by' => $legacyUser?->matched_user_id ? 'legacy_user_id' : null,
                            'product_mapping_id' => $mapping?->id,
                            'mapped_product_id' => $mapping?->product_id,
                            'granted_at' => $this->dateOrNull($order->order_tglbayar ?: $order->order_tglorder),
                            'metadata' => json_encode([
                                'legacy_order_id' => $order->order_id,
                                'order_harga' => $order->order_harga,
                                'order_hargaunik' => $order->order_hargaunik,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    if (!$this->option('skip-grant') && $status === 'resolved') {
                        $this->grantUserProductAccess(
                            (int) $legacyUser->matched_user_id,
                            (int) $mapping->product_id,
                            $order
                        );
                    }
                }
            });

        $this->info('Import product accesses selesai.');
    }

    protected function grantUserProductAccess(int $userId, int $productId, object $order): void
    {
        $existing = DB::table('user_products')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return;
        }

        $userProductId = DB::table('user_products')->insertGetId([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => null,
            'order_item_id' => null,
            'source_product_id' => null,
            'access_type' => 'legacy_import',
            'status' => 'active',
            'starts_at' => $this->dateOrNull($order->order_tglbayar ?: $order->order_tglorder),
            'expires_at' => null,
            'granted_by' => null,
            'granted_at' => now(),
            'metadata' => json_encode([
                'source' => 'EPIC HUB 1.0',
                'legacy_order_id' => $order->order_id,
                'legacy_product_id' => $order->order_idproduk,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('access_logs')->insert([
            'user_id' => $userId,
            'product_id' => $productId,
            'user_product_id' => $userProductId,
            'order_id' => null,
            'action' => 'legacy_granted',
            'actor_id' => null,
            'metadata' => json_encode([
                'source' => 'legacy_import',
                'legacy_order_id' => $order->order_id,
                'batch_id' => $this->batchId,
            ]),
            'created_at' => now(),
        ]);

        DB::table('legacy_v1_product_accesses')
            ->where('import_key', 'v1-access-order-' . $order->order_id)
            ->update([
                'granted_user_product_id' => $userProductId,
                'status' => 'granted',
                'updated_at' => now(),
            ]);
    }

    protected function importPayments(): void
    {
        $this->info('Import legacy payments...');

        $legacy = DB::connection($this->legacyConnection);

        $legacy->table('epi_payment_confirm')
            ->orderBy('id')
            ->chunk($this->chunk, function ($payments) {
                foreach ($payments as $payment) {
                    $legacyOrder = DB::table('legacy_v1_orders')
                        ->where('batch_id', $this->batchId)
                        ->where('legacy_order_id', (string) $payment->order_id)
                        ->first();

                    DB::table('legacy_v1_payments')->updateOrInsert(
                        ['import_key' => 'v1-payment-' . $payment->id],
                        [
                            'batch_id' => $this->batchId,
                            'legacy_payment_id' => (string) $payment->id,
                            'legacy_payment_number' => $payment->invoice_no,
                            'legacy_order_id' => (string) $payment->order_id,
                            'legacy_v1_order_id' => $legacyOrder?->id,
                            'legacy_user_id' => $legacyOrder?->legacy_user_id,
                            'legacy_user_epic_id' => $legacyOrder?->legacy_user_epic_id,
                            'legacy_user_email' => $legacyOrder?->legacy_customer_email,
                            'user_id' => $legacyOrder?->user_id,
                            'legacy_status' => (string) $payment->status,
                            'normalized_status' => $this->normalizePaymentStatus($payment->status),
                            'payment_method' => $payment->bank_code ?: 'manual_transfer',
                            'provider' => 'manual',
                            'provider_reference' => $payment->invoice_no,
                            'amount' => (float) $payment->nominal,
                            'currency' => 'IDR',
                            'paid_at' => $this->dateOrNull($payment->transfer_date),
                            'expired_at' => null,
                            'migration_status' => $legacyOrder ? 'resolved' : 'unresolved_order',
                            'source_note' => $payment->verified_note,
                            'raw_payload' => json_encode([
                                'file_path' => $payment->file_path,
                                'file_type' => $payment->file_type,
                                'file_name' => $payment->file_name,
                                'file_size' => $payment->file_size,
                                'bank_label' => $payment->bank_label,
                                'atas_nama' => $payment->atas_nama,
                                'created_ip' => $payment->created_ip,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Import legacy payments selesai.');
    }

    protected function importCommissions(): void
    {
        $this->info('Import legacy commissions dari sa_laporan...');

        $legacy = DB::connection($this->legacyConnection);

        $legacy->table('sa_laporan as l')
            ->leftJoin('sa_order as o', 'o.order_id', '=', 'l.lap_idorder')
            ->leftJoin('sa_page as p', 'p.page_id', '=', 'o.order_idproduk')
            ->where('l.lap_code', '2')
            ->select('l.*', 'o.order_idproduk', 'p.page_judul')
            ->orderBy('l.lap_id')
            ->chunk($this->chunk, function ($rows) {
                foreach ($rows as $row) {
                    $receiver = DB::table('legacy_v1_users')
                        ->where('batch_id', $this->batchId)
                        ->where('legacy_user_id', (string) $row->lap_idsponsor)
                        ->first();

                    $downline = DB::table('legacy_v1_users')
                        ->where('batch_id', $this->batchId)
                        ->where('legacy_user_id', (string) $row->lap_idmember)
                        ->first();

                    $mapping = null;

                    if ($row->order_idproduk) {
                        $mapping = DB::table('legacy_v1_product_mappings')
                            ->where('legacy_product_key', (string) $row->order_idproduk)
                            ->first();
                    }

                    DB::table('legacy_v1_commissions')->updateOrInsert(
                        ['import_key' => 'v1-commission-' . $row->lap_id],
                        [
                            'import_batch_id' => $this->batchId,
                            'row_number' => null,
                            'legacy_commission_id' => (string) $row->lap_id,
                            'legacy_user_epic_id' => $receiver?->normalized_epic_id,
                            'legacy_user_name' => $receiver?->normalized_name,
                            'legacy_user_email' => $receiver?->normalized_email,
                            'legacy_user_whatsapp' => $receiver?->normalized_whatsapp,
                            'user_id' => $receiver?->matched_user_id,
                            'epi_channel_id' => $receiver?->epi_channel_id,
                            'legacy_sponsor_epic_id' => $receiver?->normalized_epic_id,
                            'legacy_downline_epic_id' => $downline?->normalized_epic_id,
                            'legacy_downline_name' => $downline?->normalized_name,
                            'legacy_order_id' => (string) $row->lap_idorder,
                            'legacy_product_code' => $row->order_idproduk ? (string) $row->order_idproduk : null,
                            'legacy_product_name' => $row->page_judul,
                            'product_id' => $mapping?->product_id,
                            'commission_type' => 'legacy_sales_commission',
                            'commission_level' => $row->lap_level,
                            'commission_amount' => (float) $row->lap_masuk,
                            'commission_status' => $row->payout_id ? 'paid_or_in_payout' : 'approved',
                            'earned_at' => $this->dateOrNull($row->lap_tanggal),
                            'approved_at' => $this->dateOrNull($row->lap_tanggal),
                            'paid_at' => null,
                            'legacy_period_month' => $row->lap_tanggal ? (int) Carbon::parse($row->lap_tanggal)->format('m') : null,
                            'legacy_period_year' => $row->lap_tanggal ? (int) Carbon::parse($row->lap_tanggal)->format('Y') : null,
                            'is_payable' => 0,
                            'payout_id' => null,
                            'source_note' => $row->lap_keterangan,
                            'raw_payload' => json_encode([
                                'lap_code' => $row->lap_code,
                                'lap_reference' => $row->lap_reference,
                                'lap_app' => $row->lap_app,
                                'legacy_payout_id' => $row->payout_id,
                            ]),
                            'migration_status' => $receiver?->matched_user_id ? 'resolved' : 'unresolved_user',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Import legacy commissions selesai.');
    }

    protected function importPayouts(): void
    {
        $this->info('Import legacy payouts...');

        $legacy = DB::connection($this->legacyConnection);

        $legacy->table('epi_commission_payout')
            ->orderBy('id')
            ->chunk($this->chunk, function ($payouts) {
                foreach ($payouts as $payout) {
                    $receiver = DB::table('legacy_v1_users')
                        ->where('batch_id', $this->batchId)
                        ->where('legacy_user_id', (string) $payout->receiver_id)
                        ->first();

                    DB::table('legacy_v1_payouts')->updateOrInsert(
                        ['import_key' => 'v1-payout-' . $payout->id],
                        [
                            'batch_id' => $this->batchId,
                            'legacy_payout_id' => (string) $payout->id,
                            'legacy_user_id' => (string) $payout->receiver_id,
                            'legacy_user_epic_id' => $receiver?->normalized_epic_id,
                            'legacy_user_email' => $receiver?->normalized_email,
                            'user_id' => $receiver?->matched_user_id,
                            'epi_channel_id' => $receiver?->epi_channel_id,
                            'legacy_status' => $payout->status,
                            'normalized_status' => $this->normalizePayoutStatus($payout->status),
                            'amount' => (float) ($payout->net_amount ?: $payout->amount),
                            'requested_at' => $this->dateOrNull($payout->created_at),
                            'approved_at' => $this->dateOrNull($payout->processed_at),
                            'paid_at' => $this->dateOrNull($payout->paid_at),
                            'migration_status' => $receiver?->matched_user_id ? 'resolved' : 'unresolved_user',
                            'source_note' => $payout->note ?: $payout->reject_reason ?: $payout->cancel_reason,
                            'raw_payload' => json_encode([
                                'type' => $payout->type,
                                'gross_amount' => $payout->gross_amount,
                                'tax_percent' => $payout->tax_percent,
                                'tax_amount' => $payout->tax_amount,
                                'reject_reason' => $payout->reject_reason,
                                'cancel_reason' => $payout->cancel_reason,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

        $this->info('Import legacy payouts selesai.');
    }

    protected function report(): void
    {
        $this->info('Migration report');

        $rows = [
            ['legacy_v1_users', DB::table('legacy_v1_users')->where('batch_id', $this->batchId)->count()],
            ['users imported', DB::table('legacy_v1_users')->where('batch_id', $this->batchId)->where('status', 'imported')->count()],
            ['sponsor resolved', DB::table('legacy_v1_sponsor_links')->where('batch_id', $this->batchId)->where('resolution_status', 'applied')->count()],
            ['products mapped', DB::table('legacy_v1_product_mappings')->whereNotNull('product_id')->count()],
            ['legacy orders', DB::table('legacy_v1_orders')->where('batch_id', $this->batchId)->count()],
            ['product access granted', DB::table('legacy_v1_product_accesses')->where('batch_id', $this->batchId)->where('status', 'granted')->count()],
            ['legacy payments', DB::table('legacy_v1_payments')->where('batch_id', $this->batchId)->count()],
            ['legacy commissions', DB::table('legacy_v1_commissions')->where('import_batch_id', $this->batchId)->count()],
            ['legacy payouts', DB::table('legacy_v1_payouts')->where('batch_id', $this->batchId)->count()],
            ['import errors', DB::table('legacy_v1_import_errors')->where('batch_id', $this->batchId)->count()],
        ];

        $this->table(['Metric', 'Total'], $rows);

        DB::table('legacy_v1_import_batches')
            ->where('id', $this->batchId)
            ->update([
                'summary' => json_encode([
                    'report' => collect($rows)->mapWithKeys(fn ($row) => [$row[0] => $row[1]])->all(),
                ]),
                'updated_at' => now(),
            ]);
    }

    protected function ensureEpiChannel(int $userId, object $legacyUser): int
    {
        $epicCode = $legacyUser->normalized_epic_id ?: ('LEGACY-' . $legacyUser->legacy_user_id);

        $existing = DB::table('epi_channels')
            ->where('epic_code', $epicCode)
            ->first();

        if ($existing && (int) $existing->user_id !== $userId) {
            $epicCode = 'LEGACY-' . $legacyUser->legacy_user_id;
            $existing = DB::table('epi_channels')->where('epic_code', $epicCode)->first();
        }

        $payload = [
            'user_id' => $userId,
            'epic_code' => $epicCode,
            'store_name' => $legacyUser->normalized_name,
            'status' => 'active',
            'source' => 'legacy_import',
            'activated_at' => now(),
            'metadata' => json_encode([
                'legacy_user_id' => $legacyUser->legacy_user_id,
                'legacy_epic_id' => $legacyUser->normalized_epic_id,
                'generated_code' => !$legacyUser->normalized_epic_id,
            ]),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('epi_channels')->where('id', $existing->id)->update($payload);
            return (int) $existing->id;
        }

        $payload['created_at'] = now();
        return (int) DB::table('epi_channels')->insertGetId($payload);
    }

    protected function logError(string $scope, string $code, string $message, array $context = []): void
    {
        DB::table('legacy_v1_import_errors')->insert([
            'batch_id' => $this->batchId,
            'scope' => $scope,
            'severity' => 'warning',
            'code' => $code,
            'message' => $message,
            'context' => json_encode($context),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function guessProductType(?string $slug, ?string $title): string
    {
        $text = Str::lower(($slug ?? '') . ' ' . ($title ?? ''));

        if (str_contains($text, 'ebook')) {
            return 'ebook';
        }

        if (
            str_contains($text, 'record') ||
            str_contains($text, 'academy') ||
            str_contains($text, 'modul') ||
            str_contains($text, 'scaleup') ||
            str_contains($text, 'scale up')
        ) {
            return 'course';
        }

        if (
            str_contains($text, 'epicreg') ||
            str_contains($text, 'paket bisnis') ||
            str_contains($text, 'epic5') ||
            str_contains($text, 'epic6')
        ) {
            return 'membership';
        }

        return 'digital_file';
    }

    protected function normalizeOrderStatus(?string $status): string
    {
        return match ((string) $status) {
            '1' => 'paid',
            '0' => 'pending',
            '2' => 'cancelled',
            default => 'unknown',
        };
    }

    protected function normalizePaymentStatus($status): string
    {
        return match ((string) $status) {
            '1' => 'verified',
            '0' => 'pending',
            '-1' => 'rejected',
            default => 'unknown',
        };
    }

    protected function normalizePayoutStatus(?string $status): string
    {
        return match ((string) $status) {
            'paid' => 'paid',
            'processed' => 'approved',
            'requested', 'pending' => 'pending',
            'rejected' => 'rejected',
            'canceled' => 'cancelled',
            default => 'unknown',
        };
    }

    protected function normalizeEmail(?string $email): ?string
    {
        $email = Str::lower(trim((string) $email));
        return $email ?: null;
    }

    protected function normalizeWhatsapp(?string $whatsapp): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $whatsapp);

        if (!$number) {
            return null;
        }

        if (str_starts_with($number, '6208')) {
            $number = '62' . substr($number, 3);
        } elseif (str_starts_with($number, '08')) {
            $number = '62' . substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }

        return $number;
    }

    protected function normalizeEpicId(?string $epicId): ?string
    {
        $epicId = Str::upper(trim((string) $epicId));

        if (!$epicId) {
            return null;
        }

        return preg_match('/^EPIC[0-9]+$/', $epicId) ? $epicId : null;
    }

    protected function extractLegacyField(?string $data, string $key): ?string
    {
        if (!$data) {
            return null;
        }

        if (preg_match('/\[' . preg_quote($key, '/') . '\|([^\]]*)\]/u', $data, $matches)) {
            $value = trim($matches[1]);
            return $value !== '' ? $value : null;
        }

        return null;
    }

    protected function dateOrNull($value): ?string
    {
        if (!$value || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
}