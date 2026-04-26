<?php

namespace App\Actions\Affiliates;

use App\Models\EpiChannel;
use App\Models\Order;
use App\Models\ReferralOrder;
use App\Models\ReferralVisit;
use Illuminate\Http\Request;

class AttachReferralToOrderAction
{
    public function execute(Request $request, Order $order): ?ReferralOrder
    {
        $order->loadMissing(['user.epiChannel', 'user.referrerEpiChannel.user', 'referrerEpiChannel.user']);

        $data = $this->resolveReferralPayload($request);
        [$channel, $source] = $this->resolveAttributionChannel($order, $data);

        if (! $channel) {
            return null;
        }

        $visit = $this->resolveReferralVisit($channel, $data);

        if (! $order->referrer_epi_channel_id) {
            $order->forceFill([
                'referrer_epi_channel_id' => $channel->id,
                'referral_source' => $source,
            ])->save();
        }

        $referralOrder = ReferralOrder::query()->firstOrCreate(
            [
                'order_id' => $order->id,
            ],
            [
                'epi_channel_id' => $channel->id,
                'referral_visit_id' => $visit?->id,
                'buyer_user_id' => $order->user_id,
                'status' => \App\Enums\ReferralOrderStatus::Pending,
                'attributed_at' => now(),
                'metadata' => [
                    'referral_source' => $source,
                    'ref_at' => $data['at'] ?? null,
                    'is_house_channel' => $channel->isHouseChannel(),
                ],
            ],
        );

        return $referralOrder->refresh();
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @return array{0: ?EpiChannel, 1: string}
     */
    protected function resolveAttributionChannel(Order $order, ?array $data): array
    {
        $channel = $order->referrerEpiChannel;
        $source = (string) ($order->referral_source ?: 'order_snapshot');

        if (! $channel && $order->user?->referrerEpiChannel) {
            $channel = $order->user->referrerEpiChannel;
            $source = (string) ($order->user->referral_source ?: 'locked_user_referrer');
        }

        if (! $channel && ! $order->user?->referrer_epi_channel_id && is_array($data)) {
            $epicCode = trim((string) ($data['epic_code'] ?? ''));
            $at = (int) ($data['at'] ?? 0);

            if ($epicCode !== '' && $at > 0 && now()->timestamp - $at <= (60 * 60 * 24 * 30)) {
                $channel = EpiChannel::query()
                    ->with('user')
                    ->where('epic_code', $epicCode)
                    ->active()
                    ->first();

                $source = 'cookie';
            }
        }

        if (! $channel || $this->isSelfReferral($order, $channel)) {
            return [null, $source];
        }

        return [$channel, $source];
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    protected function resolveReferralVisit(EpiChannel $channel, ?array $data): ?ReferralVisit
    {
        if (! is_array($data)) {
            return null;
        }

        $visitId = (int) ($data['visit_id'] ?? 0);
        $epicCode = trim((string) ($data['epic_code'] ?? ''));

        if ($visitId <= 0 || $epicCode !== $channel->epic_code) {
            return null;
        }

        return ReferralVisit::query()
            ->where('id', $visitId)
            ->where('epi_channel_id', $channel->id)
            ->first();
    }

    protected function resolveReferralPayload(Request $request): ?array
    {
        foreach (['epic_ref', 'epichub_ref'] as $cookieKey) {
            $raw = (string) $request->cookie($cookieKey, '');

            if ($raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $sessionPayload = $request->session()->get('epichub_referral');

        return is_array($sessionPayload) ? $sessionPayload : null;
    }

    protected function isSelfReferral(Order $order, EpiChannel $channel): bool
    {
        if ((int) $channel->user_id === (int) $order->user_id) {
            return true;
        }

        return $order->user?->epiChannel?->is($channel) ?? false;
    }
}

