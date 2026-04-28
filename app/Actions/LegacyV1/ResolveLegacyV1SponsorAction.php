<?php

namespace App\Actions\LegacyV1;

use App\Actions\Affiliates\EnsureDefaultEpiChannelAction;
use App\Models\EpiChannel;
use App\Models\LegacyV1SponsorLink;
use App\Models\LegacyV1User;
use Illuminate\Support\Facades\DB;

class ResolveLegacyV1SponsorAction
{
    public function __construct(
        protected EnsureDefaultEpiChannelAction $ensureDefaultEpiChannel,
        protected RecordLegacyV1ImportErrorAction $recordImportError,
    ) {}

    public function execute(LegacyV1User $legacyUser, bool $force = false): LegacyV1SponsorLink
    {
        return DB::transaction(function () use ($legacyUser, $force): LegacyV1SponsorLink {
            $legacyUser = LegacyV1User::query()
                ->with(['batch', 'importedUser.epiChannel', 'importedUser.referrerEpiChannel'])
                ->whereKey($legacyUser->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $legacyUser->importedUser;

            if (! $user) {
                $legacyUser->forceFill(['sponsor_status' => 'unresolved'])->save();

                return $this->upsertSponsorLink(
                    legacyUser: $legacyUser,
                    resolutionStatus: 'unresolved',
                    resolutionReason: 'User hasil import belum tersedia untuk resolve sponsor.',
                    forced: $force,
                );
            }

            $previousChannel = $user->referrerEpiChannel()->lockForUpdate()->first();
            $previousReferralSource = $user->referral_source;
            $previousReferralLockedAt = $user->referral_locked_at?->toIso8601String();

            if ($user->referrer_epi_channel_id !== null && ! $force) {
                $legacyUser->forceFill([
                    'sponsor_status' => $this->matchesCurrentReferrer($user->referrerEpiChannel, $legacyUser->normalized_sponsor_epic_id) ? 'resolved' : 'existing_locked',
                ])->save();

                return $this->upsertSponsorLink(
                    legacyUser: $legacyUser,
                    resolutionStatus: $legacyUser->sponsor_status,
                    resolutionReason: 'Locked referrer existing dipertahankan.',
                    forced: false,
                    previousChannel: $previousChannel,
                    resolvedChannel: $user->referrerEpiChannel,
                    previousReferralSource: $previousReferralSource,
                    previousReferralLockedAt: $previousReferralLockedAt,
                );
            }

            $house = $this->ensureDefaultEpiChannel->execute();
            $resolutionStatus = 'resolved';
            $resolutionReason = 'Sponsor berhasil di-resolve dari ID EPIC legacy.';
            $resolvedChannel = null;
            $resolvedSponsorUserId = null;

            if ($legacyUser->normalized_sponsor_epic_id === null) {
                $resolvedChannel = $house;
                $resolutionStatus = 'fallback_house';
                $resolutionReason = 'Sponsor legacy kosong. Fallback ke EPIC-HOUSE.';
            } else {
                $resolvedChannel = EpiChannel::query()
                    ->where('epic_code', $legacyUser->normalized_sponsor_epic_id)
                    ->first();

                if (! $resolvedChannel) {
                    $resolvedChannel = $house;
                    $resolutionStatus = 'fallback_house';
                    $resolutionReason = 'Sponsor legacy tidak ditemukan. Fallback ke EPIC-HOUSE.';
                } elseif ((int) $resolvedChannel->user_id === (int) $user->id || $user->epiChannel?->is($resolvedChannel)) {
                    $resolvedChannel = $house;
                    $resolutionStatus = 'self_referral';
                    $resolutionReason = 'Sponsor legacy mengarah ke diri sendiri. Fallback ke EPIC-HOUSE.';
                } else {
                    $resolvedSponsorUserId = $resolvedChannel->user_id;
                }
            }

            $user->forceFill([
                'referrer_epi_channel_id' => $resolvedChannel->id,
                'referral_locked_at' => now(),
                'referral_source' => $force ? 'legacy_v1_import_force' : 'legacy_v1_import',
            ])->save();

            $legacyUser->forceFill([
                'sponsor_status' => $force && $previousChannel !== null ? 'forced' : $resolutionStatus,
            ])->save();

            $link = $this->upsertSponsorLink(
                legacyUser: $legacyUser,
                resolutionStatus: $legacyUser->sponsor_status,
                resolutionReason: $resolutionReason,
                forced: $force,
                previousChannel: $previousChannel,
                resolvedChannel: $resolvedChannel,
                resolvedSponsorUserId: $resolvedSponsorUserId,
                previousReferralSource: $previousReferralSource,
                previousReferralLockedAt: $previousReferralLockedAt,
            );

            if (in_array($resolutionStatus, ['fallback_house', 'self_referral'], true)) {
                $this->recordImportError->execute(
                    batch: $legacyUser->batch,
                    scope: 'sponsor',
                    code: $resolutionStatus,
                    message: $resolutionReason,
                    legacyUser: $legacyUser,
                    severity: 'warning',
                    context: [
                        'user_id' => $user->id,
                        'sponsor_legacy_epic_id' => $legacyUser->normalized_sponsor_epic_id,
                    ],
                );
            }

            return $link;
        });
    }

    protected function upsertSponsorLink(
        LegacyV1User $legacyUser,
        string $resolutionStatus,
        string $resolutionReason,
        bool $forced,
        ?EpiChannel $previousChannel = null,
        ?EpiChannel $resolvedChannel = null,
        ?int $resolvedSponsorUserId = null,
        ?string $previousReferralSource = null,
        ?string $previousReferralLockedAt = null,
    ): LegacyV1SponsorLink {
        return LegacyV1SponsorLink::query()->updateOrCreate(
            ['legacy_v1_user_id' => $legacyUser->id],
            [
                'batch_id' => $legacyUser->batch_id,
                'user_id' => $legacyUser->imported_user_id,
                'sponsor_legacy_epic_id' => $legacyUser->normalized_sponsor_epic_id,
                'previous_referrer_epi_channel_id' => $previousChannel?->id,
                'resolved_sponsor_user_id' => $resolvedSponsorUserId,
                'resolved_referrer_epi_channel_id' => $resolvedChannel?->id,
                'resolution_status' => $resolutionStatus,
                'forced' => $forced,
                'resolution_reason' => $resolutionReason,
                'applied_at' => now(),
                'metadata' => [
                    'previous_referral_source' => $previousReferralSource,
                    'previous_referral_locked_at' => $previousReferralLockedAt,
                ],
            ],
        );
    }

    protected function matchesCurrentReferrer(?EpiChannel $currentReferrer, ?string $legacySponsorEpicId): bool
    {
        if (! $currentReferrer || ! $legacySponsorEpicId) {
            return false;
        }

        return $currentReferrer->epic_code === $legacySponsorEpicId;
    }
}
