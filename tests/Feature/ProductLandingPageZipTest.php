<?php

use App\Actions\Catalog\ExtractProductLandingPageZipAction;
use App\Enums\EpiChannelStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\EpiChannel;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

function makeZipLandingProduct(array $overrides = []): Product
{
    return Product::query()->create(array_merge([
        'title' => 'Landing ZIP Product',
        'slug' => 'landing-zip-'.Str::lower(Str::random(8)),
        'short_description' => 'Deskripsi singkat produk ZIP',
        'full_description' => '<p>Deskripsi lengkap produk ZIP</p>',
        'product_type' => ProductType::Ebook,
        'price' => '120000.00',
        'sale_price' => '95000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
        'is_affiliate_enabled' => true,
        'affiliate_commission_type' => 'percentage',
        'affiliate_commission_value' => '10.00',
        'landing_page_enabled' => true,
        'landing_page_entry_file' => 'index.html',
        'landing_page_meta_title' => 'Offer {{product_title}}',
        'landing_page_meta_description' => 'Promo {{affiliate_name}}',
        'landing_page_version' => 1,
    ], $overrides));
}

/**
 * @param  array<string, string>  $entries
 */
function createLandingZip(string $path, array $entries): string
{
    $tempPath = tempnam(sys_get_temp_dir(), 'landing-zip-');
    $zip = new ZipArchive();
    $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($entries as $entry => $contents) {
        $zip->addFromString($entry, $contents);
    }

    $zip->close();

    Storage::disk('local')->put($path, (string) file_get_contents($tempPath));
    @unlink($tempPath);

    return $path;
}

function extractLandingZip(Product $product, string $zipPath, bool $incrementVersion = false): Product
{
    $product->forceFill([
        'landing_page_zip_path' => $zipPath,
    ])->save();

    return app(ExtractProductLandingPageZipAction::class)->execute($product, $zipPath, $incrementVersion);
}

function sampleLandingEntries(): array
{
    return [
        'index.html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <h1>{{product_title}}</h1>
    <p>{{product_short_description}}</p>
    <div class="price">{{product_effective_price}}</div>
    <a id="checkout-link" href="{{checkout_url}}">Checkout</a>
    <div class="affiliate">{{affiliate_name}}|{{affiliate_code}}|{{affiliate_store_name}}</div>
    <div class="ref">{{affiliate_referral_link}}</div>
    <script src="./assets/app.js"></script>
</body>
</html>
HTML,
        'assets/app.css' => '.price{color:#2563eb;background:url("../images/pattern.png");}',
        'assets/app.js' => 'window.zipLandingLoaded = true;',
        'images/pattern.png' => 'fake-image-content',
    ];
}

test('product landing zip enabled can be accessed', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-enabled.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer.show', $product->slug))
        ->assertOk()
        ->assertSee('Landing ZIP Product')
        ->assertSee('Rp 95.000')
        ->assertSee('/offer-assets/'.$product->fresh()->landing_page_asset_token.'/assets/app.css', false)
        ->assertSee('/offer-assets/'.$product->fresh()->landing_page_asset_token.'/assets/app.js', false);
});

test('product without landing page enabled returns 404', function () {
    $product = makeZipLandingProduct([
        'landing_page_enabled' => false,
    ]);
    $zipPath = createLandingZip('product-landings/zips/test-disabled.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer.show', $product->slug))
        ->assertNotFound();
});

test('draft private and hidden products cannot access landing page', function () {
    $zipDraft = createLandingZip('product-landings/zips/test-draft.zip', sampleLandingEntries());
    $zipPrivate = createLandingZip('product-landings/zips/test-private.zip', sampleLandingEntries());
    $zipHidden = createLandingZip('product-landings/zips/test-hidden.zip', sampleLandingEntries());

    $draft = makeZipLandingProduct([
        'slug' => 'landing-zip-draft',
        'status' => ProductStatus::Draft,
    ]);
    $private = makeZipLandingProduct([
        'slug' => 'landing-zip-private',
        'visibility' => ProductVisibility::Private,
    ]);
    $hidden = makeZipLandingProduct([
        'slug' => 'landing-zip-hidden',
        'visibility' => ProductVisibility::Hidden,
    ]);

    extractLandingZip($draft, $zipDraft);
    extractLandingZip($private, $zipPrivate);
    extractLandingZip($hidden, $zipHidden);

    $this->get(route('offer.show', $draft->slug))->assertNotFound();
    $this->get(route('offer.show', $private->slug))->assertNotFound();
    $this->get(route('offer.show', $hidden->slug))->assertNotFound();
});

test('index html product shortcode is rendered', function () {
    $product = makeZipLandingProduct([
        'title' => 'Mastering ZIP Offer',
        'short_description' => 'Belajar dari landing page ZIP',
    ]);
    $zipPath = createLandingZip('product-landings/zips/test-shortcode.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer.show', $product->slug))
        ->assertOk()
        ->assertSee('Mastering ZIP Offer')
        ->assertSee('Belajar dari landing page ZIP')
        ->assertSee(route('checkout.show', $product->slug), false);
});

test('affiliate shortcode is rendered on affiliate landing page', function () {
    $owner = User::factory()->create(['name' => 'Affiliate ZIP']);
    $channel = EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'ZIP-AFF',
        'store_name' => 'ZIP Store',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-affiliate.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code]))
        ->assertOk()
        ->assertSee('Affiliate ZIP')
        ->assertSee('ZIP-AFF')
        ->assertSee('ZIP Store');
});

test('affiliate checkout url contains ref epic code', function () {
    $owner = User::factory()->create(['name' => 'Checkout ZIP']);
    $channel = EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'ZIP-CHECKOUT',
        'store_name' => 'Checkout ZIP Store',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-checkout.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code]))
        ->assertOk()
        ->assertSee(route('checkout.show', $product->slug).'?ref=ZIP-CHECKOUT', false);
});

test('affiliate landing valid tracks referral visit and sets cookie', function () {
    $owner = User::factory()->create(['name' => 'Referral ZIP']);
    $channel = EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'ZIP-TRACK',
        'store_name' => 'Referral ZIP Store',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-track.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $response = $this->get(route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code]));

    $response->assertOk()
        ->assertCookie('epic_ref')
        ->assertCookie('epichub_ref')
        ->assertCookie('epichub_vid');

    $this->assertDatabaseHas('referral_visits', [
        'epi_channel_id' => $channel->id,
        'product_id' => $product->id,
        'referral_code' => 'ZIP-TRACK',
    ]);
});

test('invalid affiliate code returns 404', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-invalid-aff.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer.affiliate', ['product' => $product->slug, 'epicCode' => 'INVALID-ZIP']))
        ->assertNotFound();
});

test('asset from zip can be accessed through offer assets route', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-assets.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get(route('offer-assets.show', [
        'token' => $product->fresh()->landing_page_asset_token,
        'path' => 'assets/app.css',
    ]))
        ->assertOk()
        ->assertHeader('content-type', 'text/css; charset=UTF-8');
});

test('asset path traversal is rejected', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-traversal-asset.zip', sampleLandingEntries());
    extractLandingZip($product, $zipPath);

    $this->get('/offer-assets/'.$product->fresh()->landing_page_asset_token.'/../.env')
        ->assertNotFound();
});

test('zip with php file is rejected', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-php.zip', [
        'index.html' => '<h1>Hello</h1>',
        'danger.php' => '<?php echo "bad"; ?>',
    ]);

    $this->expectException(ValidationException::class);

    extractLandingZip($product, $zipPath);
});

test('zip without index html is rejected', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-missing-index.zip', [
        'assets/app.css' => 'body { color: red; }',
    ]);

    $this->expectException(ValidationException::class);

    extractLandingZip($product, $zipPath);
});

test('zip with path traversal entry is rejected', function () {
    $product = makeZipLandingProduct();
    $zipPath = createLandingZip('product-landings/zips/test-path-traversal.zip', [
        '../evil.html' => '<h1>evil</h1>',
        'index.html' => '<h1>ok</h1>',
    ]);

    $this->expectException(ValidationException::class);

    extractLandingZip($product, $zipPath);
});
