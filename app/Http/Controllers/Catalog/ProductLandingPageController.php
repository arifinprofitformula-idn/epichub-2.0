<?php

namespace App\Http\Controllers\Catalog;

use App\Actions\Affiliates\TrackReferralVisitAction;
use App\Actions\Catalog\RenderProductLandingPageAction;
use App\Http\Controllers\Controller;
use App\Models\EpiChannel;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductLandingPageController extends Controller
{
    public function __construct(
        protected RenderProductLandingPageAction $renderLandingPage,
        protected TrackReferralVisitAction $trackReferralVisit,
    ) {
    }

    public function show(Request $request, Product $product): Response|View
    {
        $product = $this->resolveProduct($product);
        $affiliateCode = $request->query('ref');

        if (filled($affiliateCode)) {
            return $this->renderAffiliateLandingPage($request, $product, (string) $affiliateCode);
        }

        return view('catalog.products.landing', [
            'product' => $product,
            'channel' => null,
            'rendered' => $this->renderLandingPage->execute($product),
        ]);
    }

    public function showAffiliate(Request $request, Product $product, string $epicCode): RedirectResponse
    {
        return redirect()->to(route('offer.show', ['product' => $product->slug]).'?ref='.urlencode($epicCode), 301);
    }

    protected function renderAffiliateLandingPage(Request $request, Product $product, string $epicCode): Response
    {
        $channel = EpiChannel::query()
            ->with('user')
            ->where('epic_code', $epicCode)
            ->active()
            ->firstOrFail();

        if ($request->user()?->epiChannel && $request->user()->epiChannel->epic_code === $channel->epic_code) {
            abort(404);
        }

        $tracked = $this->trackReferralVisit->execute(
            request: $request,
            channel: $channel,
            product: $product,
            landingUrl: route('offer.show', [
                'product' => $product->slug,
            ], false).'?ref='.urlencode($channel->epic_code),
        );

        $request->session()->put('epic_ref', $tracked['ref']);
        $request->session()->put('epichub_ref', $tracked['ref']);

        $response = response()->view('catalog.products.landing', [
            'product' => $product,
            'channel' => $channel,
            'rendered' => $this->renderLandingPage->execute($product, $channel),
        ]);

        return $response
            ->cookie(cookie('epichub_vid', $tracked['visitor_id'], $tracked['minutes']))
            ->cookie(cookie('epic_ref', json_encode($tracked['ref']), $tracked['minutes']))
            ->cookie(cookie('epichub_ref', json_encode($tracked['ref']), $tracked['minutes']));
    }

    protected function resolveProduct(Product $product): Product
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->where('landing_page_enabled', true)
            ->firstOrFail();

        abort_if(blank($product->landing_page_extract_path), 404);
        abort_if(blank($product->landing_page_entry_file), 404);
        abort_unless(Storage::disk('local')->exists(trim($product->landing_page_extract_path, '/').'/'.$product->landing_page_entry_file), 404);

        return $product;
    }
}
