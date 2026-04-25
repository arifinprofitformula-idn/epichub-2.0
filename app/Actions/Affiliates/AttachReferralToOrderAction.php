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
        $raw = (string) $request->cookie('epic_ref', '');

        if ($raw === '') {
            $raw = (string) $request->cookie('epichub_ref', '');
        }

        if ($raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return null;
        }

        $epicCode = (string) ($data['epic_code'] ?? '');
        $visitId = (int) ($data['visit_id'] ?? 0);
        $at = (int) ($data['at'] ?? 0);

        if ($epicCode === '' || $at <= 0) {
            return null;
        }

        if (now()->timestamp - $at > (60 * 60 * 24 * 30)) {
            return null;
        }

        $channel = EpiChannel::query()
            ->where('epic_code', $epicCode)
            ->active()
            ->first();

        if (! $channel) {
            return null;
        }

        if ($channel->user_id === $order->user_id) {
            return null;
        }

        $buyer = $order->user()->with('epiChannel')->first();

        if ($buyer?->epiChannel && $buyer->epiChannel->epic_code === $channel->epic_code) {
            return null;
        }

        $visit = null;

        if ($visitId > 0) {
            $visit = ReferralVisit::query()
                ->where('id', $visitId)
                ->where('epi_channel_id', $channel->id)
                ->first();
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
                    'ref_at' => $at,
                ],
            ],
        );

        return $referralOrder->refresh();
    }
}

