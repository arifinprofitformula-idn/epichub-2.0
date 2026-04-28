<?php

namespace App\Actions\LegacyV1;

use App\Actions\Access\RevokeProductAccessAction;
use App\Actions\Affiliates\EnsureDefaultEpiChannelAction;
use App\Models\LegacyV1ImportBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RollbackLegacyImportBatchAction
{
    public function __construct(
        protected RevokeProductAccessAction $revokeProductAccess,
        protected EnsureDefaultEpiChannelAction $ensureDefaultEpiChannel,
    ) {}

    /**
     * @return array{revoked_accesses:int, restored_referrers:int, untouched_created_users:int}
     */
    public function execute(LegacyV1ImportBatch $batch): array
    {
        $batch->loadMissing(['productAccesses.grantedUserProduct', 'sponsorLinks.user']);

        $actor = $batch->importedBy ?? $this->ensureDefaultEpiChannel->execute()->user;
        $revokedAccesses = 0;
        $restoredReferrers = 0;

        foreach ($batch->productAccesses as $access) {
            if (! in_array($access->status, ['granted', 'reactivated'], true) || ! $access->grantedUserProduct) {
                continue;
            }

            $this->revokeProductAccess->execute(
                $access->grantedUserProduct,
                $actor,
                sprintf('Rollback legacy import batch #%d', $batch->id),
            );

            $access->forceFill(['status' => 'rolled_back'])->save();
            $revokedAccesses++;
        }

        foreach ($batch->sponsorLinks as $link) {
            if (! $link->user) {
                continue;
            }

            DB::transaction(function () use ($link, &$restoredReferrers): void {
                $user = User::query()->whereKey($link->user_id)->lockForUpdate()->first();

                if (! $user) {
                    return;
                }

                $user->forceFill([
                    'referrer_epi_channel_id' => $link->previous_referrer_epi_channel_id,
                    'referral_source' => data_get($link->metadata, 'previous_referral_source'),
                    'referral_locked_at' => data_get($link->metadata, 'previous_referral_locked_at'),
                ])->save();

                $link->forceFill(['resolution_status' => 'rolled_back'])->save();
                $restoredReferrers++;
            });
        }

        $batch->forceFill([
            'status' => 'rolled_back',
            'rolled_back_at' => now(),
        ])->save();

        return [
            'revoked_accesses' => $revokedAccesses,
            'restored_referrers' => $restoredReferrers,
            'untouched_created_users' => $batch->legacyUsers()->where('match_status', 'created')->count(),
        ];
    }
}
