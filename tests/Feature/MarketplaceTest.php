<?php

use App\Enums\EventRegistrationStatus;
use App\Enums\EventStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Str;

test('guest tidak bisa membuka marketplace', function () {
    $this->get(route('marketplace.index'))
        ->assertRedirect(route('login'));
});

test('login user bisa membuka marketplace', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee('Marketplace');
});

test('published public product tampil', function () {
    $user = User::factory()->create();
    $product = marketplaceCreateProduct('published-public-product');

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee($product->title);
});

test('draft private hidden product tidak tampil', function () {
    $user = User::factory()->create();
    $visible = marketplaceCreateProduct('visible-product');

    marketplaceCreateProduct('draft-product', [
        'status' => ProductStatus::Draft,
    ]);

    marketplaceCreateProduct('private-product', [
        'visibility' => ProductVisibility::Private,
    ]);

    marketplaceCreateProduct('hidden-product', [
        'visibility' => ProductVisibility::Hidden,
    ]);

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee($visible->title)
        ->assertDontSee('Draft Product')
        ->assertDontSee('Private Product')
        ->assertDontSee('Hidden Product');
});

test('filter category bekerja', function () {
    $user = User::factory()->create();
    $ebookCategory = marketplaceCreateCategory('ebook-kategori');
    $eventCategory = marketplaceCreateCategory('event-kategori');

    $ebook = marketplaceCreateProduct('ebook-category-product', [
        'product_category_id' => $ebookCategory->id,
    ]);

    marketplaceCreateProduct('event-category-product', [
        'product_category_id' => $eventCategory->id,
    ]);

    $this->actingAs($user)
        ->get(route('marketplace.index', ['category' => $ebookCategory->slug]))
        ->assertOk()
        ->assertSee($ebook->title)
        ->assertDontSee('Event Category Product');
});

test('filter product type bekerja', function () {
    $user = User::factory()->create();
    $course = marketplaceCreateProduct('course-filter-product', [
        'product_type' => ProductType::Course,
    ]);
    marketplaceCreateProduct('ebook-filter-product', [
        'product_type' => ProductType::Ebook,
    ]);

    $this->actingAs($user)
        ->get(route('marketplace.index', ['product_type' => ProductType::Course->value]))
        ->assertOk()
        ->assertSee($course->title)
        ->assertDontSee('Ebook Filter Product');
});

test('filter owned hanya tampilkan produk yang sudah dimiliki', function () {
    $user = User::factory()->create();
    $owned = marketplaceCreateProduct('owned-only-product');
    marketplaceCreateProduct('not-owned-only-product');
    marketplaceGrantUserProduct($user, $owned);

    $this->actingAs($user)
        ->get(route('marketplace.index', ['ownership' => 'owned']))
        ->assertOk()
        ->assertSee($owned->title)
        ->assertDontSee('Not Owned Only Product');
});

test('filter not owned menyembunyikan produk yang sudah dimiliki', function () {
    $user = User::factory()->create();
    $owned = marketplaceCreateProduct('owned-hidden-product');
    $notOwned = marketplaceCreateProduct('not-owned-visible-product');
    marketplaceGrantUserProduct($user, $owned);

    $this->actingAs($user)
        ->get(route('marketplace.index', ['ownership' => 'not_owned']))
        ->assertOk()
        ->assertSee($notOwned->title)
        ->assertDontSee($owned->title);
});

test('owned product menampilkan tombol akses', function () {
    $user = User::factory()->create();
    $product = marketplaceCreateProduct('owned-ebook-product', [
        'product_type' => ProductType::Ebook,
    ]);
    $userProduct = marketplaceGrantUserProduct($user, $product);

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee('Akses Ebook')
        ->assertSee(route('my-products.show', $userProduct), false);
});

test('not owned product menampilkan tombol beli', function () {
    $user = User::factory()->create();
    $product = marketplaceCreateProduct('not-owned-buy-product');

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee('Beli Sekarang')
        ->assertSee(route('checkout.show', $product->slug), false);
});

test('course owned mengarah ke kelas saya', function () {
    $user = User::factory()->create();
    $product = marketplaceCreateProduct('owned-course-product', [
        'product_type' => ProductType::Course,
    ]);
    $userProduct = marketplaceGrantUserProduct($user, $product);

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee('Masuk Kelas')
        ->assertSee(route('my-courses.show', $userProduct), false);
});

test('ebook digital bundle owned mengarah ke produk saya', function () {
    $user = User::factory()->create();

    $ebook = marketplaceCreateProduct('owned-ebook-route', ['product_type' => ProductType::Ebook]);
    $digital = marketplaceCreateProduct('owned-digital-route', ['product_type' => ProductType::DigitalFile]);
    $bundle = marketplaceCreateProduct('owned-bundle-route', ['product_type' => ProductType::Bundle]);

    $ebookUserProduct = marketplaceGrantUserProduct($user, $ebook);
    $digitalUserProduct = marketplaceGrantUserProduct($user, $digital);
    $bundleUserProduct = marketplaceGrantUserProduct($user, $bundle);

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee(route('my-products.show', $ebookUserProduct), false)
        ->assertSee(route('my-products.show', $digitalUserProduct), false)
        ->assertSee(route('my-products.show', $bundleUserProduct), false);
});

test('event owned mengarah ke event saya jika registration tersedia', function () {
    $user = User::factory()->create();
    $product = marketplaceCreateProduct('owned-event-product', [
        'product_type' => ProductType::Event,
    ]);
    $userProduct = marketplaceGrantUserProduct($user, $product);
    $event = marketplaceCreateEvent($product);
    $registration = EventRegistration::query()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'status' => EventRegistrationStatus::Registered,
        'registered_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee('Lihat Event')
        ->assertSee(route('my-events.show', $registration), false);
});

function marketplaceCreateCategory(string $slug): ProductCategory
{
    return ProductCategory::query()->create([
        'name' => 'Kategori '.Str::upper(Str::substr(md5($slug), 0, 6)),
        'slug' => $slug,
        'is_active' => true,
        'sort_order' => 1,
    ]);
}

function marketplaceCreateProduct(string $slug, array $overrides = []): Product
{
    $category = marketplaceCreateCategory('category-'.$slug);

    return Product::query()->create(array_merge([
        'product_category_id' => $category->id,
        'title' => Str::headline(str_replace('-', ' ', $slug)),
        'slug' => $slug,
        'short_description' => 'Deskripsi singkat '.$slug,
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'sale_price' => '85000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
        'is_featured' => true,
    ], $overrides));
}

function marketplaceGrantUserProduct(User $user, Product $product): UserProduct
{
    return UserProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);
}

function marketplaceCreateEvent(Product $product): Event
{
    return Event::query()->create([
        'product_id' => $product->id,
        'title' => 'Event '.Str::headline($product->slug),
        'slug' => 'event-'.Str::slug($product->slug).'-'.Str::lower(Str::random(6)),
        'status' => EventStatus::Published,
        'starts_at' => now()->addDays(7),
        'timezone' => 'Asia/Jakarta',
        'quota' => 100,
        'published_at' => now(),
    ]);
}
