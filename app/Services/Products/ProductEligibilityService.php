<?php

namespace App\Services\Products;

use App\Enums\EpiChannelStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProductEligibilityService
{
    /**
     * Kembalikan array audience types untuk user yang diberikan.
     * Contoh return: ['authenticated_user', 'customer', 'epi_channel_active', 'role:admin', 'user:5']
     *
     * @return array<string>
     */
    public function getUserAudienceTypes(?User $user): array
    {
        if ($user === null) {
            return ['guest'];
        }

        $types = ['authenticated_user'];

        // Role-based audiences
        if ($user->hasAnyRole(['super_admin', 'admin', 'finance', 'operator'])) {
            $types[] = 'admin';
        }

        if ($user->hasRole('customer')) {
            $types[] = 'customer';
        }

        // EPI Channel status
        $user->loadMissing('epiChannel');
        if ($user->epiChannel !== null) {
            if ($user->epiChannel->status === EpiChannelStatus::Active) {
                $types[] = 'epi_channel_active';
            } else {
                $types[] = 'epi_channel_inactive';
            }
        }

        // Contributor (via role atau contributor_user_id di produk - cukup lewat role)
        if ($user->hasRole('contributor')) {
            $types[] = 'contributor';
        }

        // Spatie roles individual
        foreach ($user->getRoleNames() as $roleName) {
            $types[] = 'role:' . $roleName;
        }

        // User ID spesifik
        $types[] = 'user:' . $user->id;

        return array_unique($types);
    }

    /**
     * Apakah user bisa melihat produk ini?
     */
    public function canView(?User $user, Product $product): bool
    {
        $mode = $product->visibility_mode ?? 'public';

        return match ($mode) {
            'public' => true,
            'hidden' => false,
            'logged_in_only' => $user !== null,
            'selected_audience' => $this->userMatchesAudience(
                $user,
                $product->allowed_viewer_types ?? [],
                $product->allowed_role_ids ?? [],
                $product->allowed_user_ids ?? [],
            ),
            default => true,
        };
    }

    /**
     * Apakah user bisa membeli produk ini (masuk checkout)?
     */
    public function canPurchase(?User $user, Product $product): bool
    {
        $mode = $product->purchase_mode ?? 'everyone';

        return match ($mode) {
            'everyone' => true,
            'disabled' => false,
            'logged_in_only' => $user !== null,
            'selected_audience' => $this->userMatchesAudience(
                $user,
                $product->allowed_buyer_types ?? [],
                $product->allowed_role_ids ?? [],
                $product->allowed_user_ids ?? [],
            ),
            default => true,
        };
    }

    /**
     * Apakah user bisa mengakses produk (di atas entitlement aktif)?
     * Catatan: entitlement aktif tetap WAJIB dicek terpisah oleh caller.
     * Method ini hanya menambah lapisan audience check jika diperlukan.
     */
    public function canAccess(?User $user, Product $product): bool
    {
        $mode = $product->access_mode ?? 'entitlement_only';

        if ($mode === 'entitlement_only') {
            return true;
        }

        // entitlement_and_selected_audience
        return $this->userMatchesAudience(
            $user,
            $product->allowed_access_types ?? [],
            $product->allowed_role_ids ?? [],
            $product->allowed_user_ids ?? [],
        );
    }

    /**
     * Pesan mengapa user tidak eligible untuk konteks tertentu.
     *
     * @param string $context 'view'|'purchase'|'access'
     */
    public function getIneligibleReason(?User $user, Product $product, string $context): string
    {
        if (filled($product->ineligible_message)) {
            return $product->ineligible_message;
        }

        if ($user === null) {
            return 'Silakan login untuk ' . ($context === 'view' ? 'melihat' : ($context === 'purchase' ? 'membeli' : 'mengakses')) . ' produk ini.';
        }

        $userTypes = $this->getUserAudienceTypes($user);

        if (in_array('epi_channel_inactive', $userTypes, true)) {
            return 'Produk ini hanya tersedia untuk EPI Channel aktif.';
        }

        return 'Produk ini hanya tersedia untuk kategori pengguna tertentu.';
    }

    /**
     * Scope query agar hanya menampilkan produk yang bisa dilihat user.
     */
    public function scopeVisibleProducts(Builder $query, ?User $user): Builder
    {
        $userTypes = $this->getUserAudienceTypes($user);
        $userId    = $user?->id;

        return $query->where(function (Builder $q) use ($userTypes, $userId): void {
            // public atau tidak ada mode (default)
            $q->whereIn('visibility_mode', ['public'])
              ->orWhereNull('visibility_mode');

            // logged_in_only — user sudah login
            if ($userId !== null) {
                $q->orWhere('visibility_mode', 'logged_in_only');
            }

            // selected_audience — periksa overlap
            $q->orWhere(function (Builder $inner) use ($userTypes, $userId): void {
                $inner->where('visibility_mode', 'selected_audience')
                    ->where(function (Builder $audienceQ) use ($userTypes, $userId): void {
                        $this->applyAudienceJsonConditions($audienceQ, $userTypes, $userId, 'viewer');
                    });
            });

            // 'hidden' tidak ditambahkan → tidak muncul
        });
    }

    /**
     * Scope query agar hanya menampilkan produk yang bisa dibeli user.
     */
    public function scopePurchasableProducts(Builder $query, ?User $user): Builder
    {
        $userTypes = $this->getUserAudienceTypes($user);
        $userId    = $user?->id;

        return $query->where(function (Builder $q) use ($userTypes, $userId): void {
            $q->whereIn('purchase_mode', ['everyone'])
              ->orWhereNull('purchase_mode');

            if ($userId !== null) {
                $q->orWhere('purchase_mode', 'logged_in_only');
            }

            $q->orWhere(function (Builder $inner) use ($userTypes, $userId): void {
                $inner->where('purchase_mode', 'selected_audience')
                    ->where(function (Builder $audienceQ) use ($userTypes, $userId): void {
                        $this->applyAudienceJsonConditions($audienceQ, $userTypes, $userId, 'buyer');
                    });
            });
        });
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * @param  array<string>  $audienceTypes
     * @param  array<int|string>  $roleIds
     * @param  array<int|string>  $userIds
     */
    private function userMatchesAudience(?User $user, array $audienceTypes, array $roleIds, array $userIds): bool
    {
        if (empty($audienceTypes) && empty($roleIds) && empty($userIds)) {
            return true;
        }

        $userAudienceTypes = $this->getUserAudienceTypes($user);

        // Cek audience type overlap
        foreach ($audienceTypes as $allowed) {
            if (in_array($allowed, $userAudienceTypes, true)) {
                return true;
            }
        }

        if ($user === null) {
            return false;
        }

        // Cek role IDs spesifik via Spatie
        if (! empty($roleIds)) {
            foreach ($user->getRoleNames() as $roleName) {
                $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                if ($role && in_array((string) $role->id, array_map('strval', $roleIds), true)) {
                    return true;
                }
            }
        }

        // Cek user ID spesifik
        if (! empty($userIds) && in_array((string) $user->id, array_map('strval', $userIds), true)) {
            return true;
        }

        return false;
    }

    /**
     * Tambahkan kondisi JSON-contains ke query untuk audience check.
     *
     * @param  array<string>  $userTypes
     */
    private function applyAudienceJsonConditions(Builder $query, array $userTypes, ?int $userId, string $field): void
    {
        // allowed_{field}_types mengandung salah satu tipe user
        $query->where(function (Builder $q) use ($userTypes, $field): void {
            foreach ($userTypes as $type) {
                $q->orWhereJsonContains("allowed_{$field}_types", $type);
            }

            // Jika allowed_{field}_types kosong/null, anggap tidak membatasi
            // Gunakan perbandingan string '[]' agar kompatibel dengan SQLite & MySQL
            $q->orWhereNull("allowed_{$field}_types")
              ->orWhere("allowed_{$field}_types", '[]')
              ->orWhere("allowed_{$field}_types", '');
        });

        // Atau user ID ada di allowed_user_ids
        if ($userId !== null) {
            $query->orWhereJsonContains('allowed_user_ids', (string) $userId)
                  ->orWhereJsonContains('allowed_user_ids', $userId);
        }
    }
}
