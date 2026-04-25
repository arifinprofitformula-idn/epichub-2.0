<?php

namespace App\Actions\Affiliates;

use App\Models\EpiChannel;
use App\Models\Product;
use App\Models\ReferralVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackReferralVisitAction
{
    /**
     * @return array{visit: ReferralVisit, visitor_id: string, ref: array{epic_code: string, visit_id: int, at: int}, minutes: int}
     */
    public function execute(Request $request, EpiChannel $channel, ?Product $product, string $landingUrl): array
    {
        $visitorId = (string) $request->cookie('epichub_vid', '');

        if ($visitorId === '') {
            $visitorId = (string) Str::uuid();
        }

        $visit = ReferralVisit::query()->create([
            'epi_channel_id' => $channel->id,
            'product_id' => $product?->id,
            'referral_code' => $channel->epic_code,
            'landing_url' => $landingUrl,
            'source_url' => (string) $request->headers->get('referer'),
            'visitor_id' => $visitorId,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'clicked_at' => now(),
        ]);

        $refData = [
            'epic_code' => $channel->epic_code,
            'visit_id' => $visit->id,
            'at' => now()->timestamp,
        ];

        return [
            'visit' => $visit,
            'visitor_id' => $visitorId,
            'ref' => $refData,
            'minutes' => 60 * 24 * 30,
        ];
    }
}

