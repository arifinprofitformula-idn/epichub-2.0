<?php

namespace App\Http\Controllers;

use App\Actions\Affiliates\TrackReferralVisitAction;
use App\Models\EpiChannel;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function redirect(Request $request, string $epicCode): RedirectResponse
    {
        $channel = EpiChannel::query()
            ->where('epic_code', $epicCode)
            ->active()
            ->first();

        $defaultTo = route('catalog.products.index', absolute: false);
        $to = (string) $request->query('to', $defaultTo);

        $product = null;
        $productSlug = trim((string) $request->query('product', ''));

        if ($productSlug !== '') {
            $product = Product::query()->where('slug', $productSlug)->first();

            if ($product) {
                $to = route('catalog.products.show', ['product' => $product->slug], absolute: false);
            }
        }

        if (! $this->isSafeInternalPath($to)) {
            $to = $defaultTo;
        }

        if (! $channel) {
            return redirect()->to($to);
        }

        $user = $request->user();

        if ($user && $user->epiChannel && $user->epiChannel->epic_code === $channel->epic_code) {
            return redirect()->to($to);
        }

        $tracked = app(TrackReferralVisitAction::class)->execute(
            request: $request,
            channel: $channel,
            product: $product,
            landingUrl: $to,
        );

        return redirect()
            ->to($to)
            ->withCookie(cookie('epichub_vid', $tracked['visitor_id'], $tracked['minutes']))
            ->withCookie(cookie('epic_ref', json_encode($tracked['ref']), $tracked['minutes']))
            ->withCookie(cookie('epichub_ref', json_encode($tracked['ref']), $tracked['minutes']));
    }

    protected function isSafeInternalPath(string $to): bool
    {
        if ($to === '') {
            return false;
        }

        if (Str::startsWith($to, ['http://', 'https://'])) {
            return false;
        }

        return Str::startsWith($to, '/');
    }
}

