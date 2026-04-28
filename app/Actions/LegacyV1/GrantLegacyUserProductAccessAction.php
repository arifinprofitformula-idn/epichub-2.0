<?php

namespace App\Actions\LegacyV1;

use App\Actions\Access\GrantProductAccessAction;
use App\Enums\AccessLogAction;
use App\Models\LegacyV1ProductAccess;
use App\Models\LegacyV1ProductMapping;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class GrantLegacyUserProductAccessAction
{
    public function __construct(
        protected GrantProductAccessAction $grantProductAccess,
        protected ResolveLegacyV1UserMatchAction $resolveUserMatch,
        protected RecordLegacyV1ImportErrorAction $recordImportError,
    ) {}

    public function execute(LegacyV1ProductAccess $legacyProductAccess): LegacyV1ProductAccess
    {
        return DB::transaction(function () use ($legacyProductAccess): LegacyV1ProductAccess {
            $legacyProductAccess = LegacyV1ProductAccess::query()
                ->with(['batch.importedBy', 'mappedProduct'])
                ->whereKey($legacyProductAccess->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($legacyProductAccess->status, ['granted', 'reactivated', 'duplicate'], true) && $legacyProductAccess->granted_user_product_id !== null) {
                return $legacyProductAccess;
            }

            $match = $this->resolveUserMatch->execute(
                epicId: $legacyProductAccess->normalized_epic_id,
                email: $legacyProductAccess->normalized_email,
                whatsapp: $legacyProductAccess->normalized_whatsapp,
            );

            if ($match['conflict'] !== null) {
                return $this->markIssue($legacyProductAccess, 'conflict', 'identity_conflict', $match['conflict'], 'conflict');
            }

            $user = $match['user'];

            if (! $user instanceof User) {
                return $this->markIssue(
                    $legacyProductAccess,
                    'unresolved_user',
                    'user_not_found',
                    'User target belum ditemukan untuk grant akses legacy.',
                    'warning',
                );
            }

            $mapping = $legacyProductAccess->productMapping
                ?? ($legacyProductAccess->normalized_legacy_product_key
                    ? LegacyV1ProductMapping::query()->where('legacy_product_key', $legacyProductAccess->normalized_legacy_product_key)->where('is_active', true)->first()
                    : null);

            if (! $mapping?->product_id || ! $mapping->product) {
                return $this->markIssue(
                    $legacyProductAccess,
                    'unmapped_product',
                    'product_mapping_missing',
                    'Produk legacy belum dimapping ke produk EPIC HUB 2.0.',
                    'warning',
                );
            }

            $existingAccess = UserProduct::query()
                ->where('user_id', $user->id)
                ->where('product_id', $mapping->product_id)
                ->whereNull('order_id')
                ->latest('id')
                ->first();

            $hadActiveAccess = $existingAccess?->isActive() ?? false;
            $hadPreviousAccess = $existingAccess !== null;

            try {
                $userProduct = $this->grantProductAccess->execute(
                    user: $user,
                    product: $mapping->product,
                    actor: $legacyProductAccess->batch->importedBy,
                    logAction: AccessLogAction::ImportedGrant,
                    metadata: [
                        'source' => 'legacy_import',
                        'legacy_batch_id' => $legacyProductAccess->batch_id,
                        'legacy_access_id' => $legacyProductAccess->id,
                        'legacy_product_key' => $legacyProductAccess->normalized_legacy_product_key,
                    ],
                    grantedAt: $this->parseGrantedAt($legacyProductAccess->raw_granted_at),
                );
            } catch (Throwable $exception) {
                return $this->markIssue($legacyProductAccess, 'error', 'grant_failed', $exception->getMessage(), 'error');
            }

            $legacyProductAccess->forceFill([
                'status' => $hadActiveAccess ? 'duplicate' : ($hadPreviousAccess ? 'reactivated' : 'granted'),
                'matched_user_id' => $user->id,
                'matched_by' => $match['matched_by'],
                'product_mapping_id' => $mapping->id,
                'mapped_product_id' => $mapping->product_id,
                'granted_user_product_id' => $userProduct->id,
                'granted_at' => $userProduct->granted_at,
            ])->save();

            return $legacyProductAccess->fresh();
        });
    }

    protected function parseGrantedAt(?string $rawGrantedAt): ?Carbon
    {
        if (! $rawGrantedAt) {
            return null;
        }

        return Carbon::parse($rawGrantedAt);
    }

    protected function markIssue(
        LegacyV1ProductAccess $legacyProductAccess,
        string $status,
        string $code,
        string $message,
        string $severity,
    ): LegacyV1ProductAccess {
        $legacyProductAccess->forceFill(['status' => $status])->save();

        $this->recordImportError->execute(
            batch: $legacyProductAccess->batch,
            scope: 'access',
            code: $code,
            message: $message,
            legacyProductAccess: $legacyProductAccess,
            severity: $severity,
        );

        return $legacyProductAccess->fresh();
    }
}
