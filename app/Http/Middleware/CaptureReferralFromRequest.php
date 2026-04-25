<?php

namespace App\Http\Middleware;

use App\Actions\Affiliates\TrackReferralVisitAction;
use App\Models\EpiChannel;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferralFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $epicCode = trim((string) $request->query('ref', ''));

        if ($epicCode === '') {
            return $next($request);
        }

        $channel = EpiChannel::query()
            ->where('epic_code', $epicCode)
            ->active()
            ->first();

        if (! $channel) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->epiChannel && $user->epiChannel->epic_code === $channel->epic_code) {
            return $next($request);
        }

        $product = $request->route('product');

        if (! $product instanceof Product) {
            $product = null;
        }

        $landingUrl = $request->fullUrlWithoutQuery(['ref']);

        $tracked = app(TrackReferralVisitAction::class)->execute(
            request: $request,
            channel: $channel,
            product: $product,
            landingUrl: $landingUrl,
        );

        cookie()->queue(cookie('epichub_vid', $tracked['visitor_id'], $tracked['minutes']));
        cookie()->queue(cookie('epic_ref', json_encode($tracked['ref']), $tracked['minutes']));
        cookie()->queue(cookie('epichub_ref', json_encode($tracked['ref']), $tracked['minutes']));

        return $next($request);
    }
}

