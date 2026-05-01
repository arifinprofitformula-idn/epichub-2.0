<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use App\Models\EpiChannel;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EpicFreshCleanLegacyCommand extends Command
{
    protected $signature = 'epic:fresh-clean-legacy {--force : Jalankan cleanup tanpa prompt konfirmasi}';

    protected $description = 'Bersihkan data hasil percobaan import legacy EPIC HUB 1.0 dari tabel utama EPIC HUB 2.0.';

    public function handle(): int
    {
        $summary = $this->buildSummary();

        $this->table(
            ['Target Cleanup', 'Jumlah'],
            [
                ['Users hasil import legacy', $summary['users_to_delete']],
                ['EPI Channel legacy', $summary['epi_channels_to_delete']],
                ['User products legacy import', $summary['user_products_to_delete']],
                ['Access logs legacy', $summary['access_logs_to_delete']],
                ['Products metadata legacy', $summary['products_metadata_to_clean']],
                ['Users referral legacy yang di-reset', $summary['users_referral_to_reset']],
            ],
        );

        if (array_sum($summary) === 0) {
            $this->info('Tidak ada data legacy yang terdeteksi untuk dibersihkan.');

            return self::SUCCESS;
        }

        $this->warn('Akun admin utama, role/permission, dan channel house EPIC-HOUSE akan dipertahankan.');

        if (! $this->option('force') && ! $this->confirm('Lanjutkan cleanup data legacy sekarang?', false)) {
            $this->info('Cleanup dibatalkan.');

            return self::SUCCESS;
        }

        $result = DB::transaction(function (): array {
            $adminUserIds = $this->adminUserIds();
            $accessLogIds = $this->legacyAccessLogIds();
            $productIds = $this->legacyProductMetadataIds();

            $deletedAccessLogs = empty($accessLogIds)
                ? 0
                : AccessLog::query()->whereKey($accessLogIds)->delete();

            $deletedUserProducts = $this->legacyUserProductQuery()->forceDelete();

            $resetReferralUsers = $this->legacyReferralUsersQuery($adminUserIds)->update([
                'referrer_epi_channel_id' => null,
                'referral_locked_at' => null,
                'referral_source' => null,
            ]);

            $deletedUsers = $this->legacyUserQuery($adminUserIds)->delete();
            $deletedEpiChannels = $this->legacyEpiChannelQuery($adminUserIds)->forceDelete();
            $cleanedProducts = $this->cleanLegacyProductMetadata($productIds);

            return [
                'deleted_users' => $deletedUsers,
                'deleted_epi_channels' => $deletedEpiChannels,
                'deleted_user_products' => $deletedUserProducts,
                'deleted_access_logs' => $deletedAccessLogs,
                'cleaned_products' => $cleanedProducts,
                'reset_referral_users' => $resetReferralUsers,
            ];
        });

        $this->table(
            ['Hasil Cleanup', 'Jumlah'],
            [
                ['Users terhapus', $result['deleted_users']],
                ['EPI Channel terhapus', $result['deleted_epi_channels']],
                ['User products terhapus', $result['deleted_user_products']],
                ['Access logs terhapus', $result['deleted_access_logs']],
                ['Products metadata dibersihkan', $result['cleaned_products']],
                ['Users referral di-reset', $result['reset_referral_users']],
            ],
        );

        $this->info('Cleanup legacy selesai.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    protected function buildSummary(): array
    {
        $adminUserIds = $this->adminUserIds();

        return [
            'users_to_delete' => $this->legacyUserQuery($adminUserIds)->count(),
            'epi_channels_to_delete' => $this->legacyEpiChannelQuery($adminUserIds)->count(),
            'user_products_to_delete' => $this->legacyUserProductQuery()->count(),
            'access_logs_to_delete' => count($this->legacyAccessLogIds()),
            'products_metadata_to_clean' => count($this->legacyProductMetadataIds()),
            'users_referral_to_reset' => $this->legacyReferralUsersQuery($adminUserIds)->count(),
        ];
    }

    /**
     * @param  array<int, int>  $adminUserIds
     */
    protected function legacyUserQuery(array $adminUserIds): Builder
    {
        $query = User::query()->whereNotIn('id', $adminUserIds);
        $hasCondition = false;

        return $query->where(function (Builder $legacyQuery) use (&$hasCondition): void {
            if (Schema::hasColumn('users', 'legacy_source')) {
                $legacyQuery->orWhereIn('legacy_source', ['epic_hub_1', 'legacy_v1']);
                $hasCondition = true;
            }

            if (Schema::hasColumn('users', 'legacy_import_batch_id')) {
                $legacyQuery->orWhereNotNull('legacy_import_batch_id');
                $hasCondition = true;
            }

            if (Schema::hasColumn('users', 'legacy_user_id')) {
                $legacyQuery->orWhereNotNull('legacy_user_id');
                $hasCondition = true;
            }

            if (Schema::hasColumn('users', 'legacy_epic_id')) {
                $legacyQuery->orWhereNotNull('legacy_epic_id');
                $hasCondition = true;
            }
        })->when(! $hasCondition, fn (Builder $builder) => $builder->whereRaw('1 = 0'));
    }

    /**
     * @param  array<int, int>  $adminUserIds
     */
    protected function legacyEpiChannelQuery(array $adminUserIds): Builder
    {
        $houseEpicCode = (string) config('epichub.default_referrer_epic_code', 'EPIC-HOUSE');

        return EpiChannel::query()
            ->where('epic_code', '!=', $houseEpicCode)
            ->whereNotIn('user_id', $adminUserIds)
            ->whereIn('source', ['legacy_import', 'legacy_v1']);
    }

    protected function legacyUserProductQuery(): Builder
    {
        return UserProduct::query()
            ->where('access_type', 'legacy_import');
    }

    /**
     * @param  array<int, int>  $adminUserIds
     */
    protected function legacyReferralUsersQuery(array $adminUserIds): Builder
    {
        if (! Schema::hasColumn('users', 'referral_source')) {
            return User::query()->whereRaw('1 = 0');
        }

        return User::query()
            ->whereNotIn('id', $adminUserIds)
            ->where(function (Builder $query): void {
                $query
                    ->where('referral_source', 'legacy_import')
                    ->orWhere('referral_source', 'legacy_v1_import')
                    ->orWhere('referral_source', 'legacy_v1_import_force');
            });
    }

    /**
     * @return array<int, int>
     */
    protected function adminUserIds(): array
    {
        return User::query()
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', ['super_admin', 'admin']))
                    ->orWhere('email', 'admin@epichub.test');
            })
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function legacyAccessLogIds(): array
    {
        $ids = [];

        foreach (AccessLog::query()->select(['id', 'action', 'metadata'])->lazyById() as $log) {
            $metadata = is_array($log->metadata) ? $log->metadata : [];

            if ($log->action === 'legacy_granted'
                || data_get($metadata, 'source') === 'legacy_import'
                || data_get($metadata, 'legacy_import') === true) {
                $ids[] = (int) $log->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<int, int>
     */
    protected function legacyProductMetadataIds(): array
    {
        $ids = [];

        foreach (Product::query()->select(['id', 'metadata'])->lazyById() as $product) {
            $metadata = is_array($product->metadata) ? $product->metadata : [];

            if (array_key_exists('legacy_source', $metadata) || array_key_exists('legacy_page_id', $metadata)) {
                $ids[] = (int) $product->id;
            }
        }

        return $ids;
    }

    /**
     * @param  array<int, int>  $productIds
     */
    protected function cleanLegacyProductMetadata(array $productIds): int
    {
        $cleaned = 0;

        foreach (Product::query()->whereKey($productIds)->get(['id', 'metadata']) as $product) {
            $metadata = is_array($product->metadata) ? $product->metadata : [];

            unset($metadata['legacy_source'], $metadata['legacy_page_id']);

            $product->forceFill([
                'metadata' => $metadata === [] ? null : $metadata,
            ])->save();

            $cleaned++;
        }

        return $cleaned;
    }
};
