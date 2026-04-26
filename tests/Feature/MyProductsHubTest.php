<?php

use App\Enums\CourseStatus;
use App\Enums\EventRegistrationStatus;
use App\Enums\EventStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\User;
use App\Models\UserProduct;

function makeHubProduct(array $overrides = []): Product
{
    $type = $overrides['product_type'] ?? ProductType::Ebook;
    $slug = $overrides['slug'] ?? 'hub-product-'.strtolower($type instanceof BackedEnum ? $type->value : (string) $type).'-'.uniqid();

    return Product::query()->create(array_merge([
        'title' => 'Hub Product',
        'slug' => $slug,
        'product_type' => $type,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ], $overrides));
}

function makeHubUserProduct(User $user, Product $product, array $overrides = []): UserProduct
{
    return UserProduct::query()->create(array_merge([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ], $overrides));
}

function makeHubCourse(Product $product, array $overrides = []): Course
{
    return Course::query()->create(array_merge([
        'product_id' => $product->id,
        'title' => 'Kelas Premium',
        'slug' => 'kelas-premium-'.uniqid(),
        'status' => CourseStatus::Published,
        'published_at' => now(),
    ], $overrides));
}

function makeHubEvent(Product $product, array $overrides = []): Event
{
    return Event::query()->create(array_merge([
        'product_id' => $product->id,
        'title' => 'Event Premium',
        'slug' => 'event-premium-'.uniqid(),
        'status' => EventStatus::Published,
        'published_at' => now(),
        'starts_at' => now()->addWeek(),
        'zoom_url' => 'https://zoom.us/j/123456',
        'zoom_meeting_id' => '123456',
        'zoom_passcode' => 'secret',
    ], $overrides));
}

test('guest tidak bisa membuka produk saya', function () {
    $this->get(route('my-products.index'))
        ->assertRedirect(route('login'));
});

test('user login bisa membuka produk saya', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee('Produk Saya');
});

test('hanya produk milik user login yang tampil', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $ownedProduct = makeHubProduct(['title' => 'Ebook Milik Saya']);
    $otherProduct = makeHubProduct(['title' => 'Ebook User Lain', 'slug' => 'ebook-user-lain-'.uniqid()]);

    makeHubUserProduct($user, $ownedProduct);
    makeHubUserProduct($other, $otherProduct);

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee('Ebook Milik Saya')
        ->assertDontSee('Ebook User Lain');
});

test('produk milik user lain tidak tampil', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $product = makeHubProduct(['title' => 'Rahasia User Lain']);
    makeHubUserProduct($other, $product);

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertDontSee('Rahasia User Lain');
});

test('filter product_type course bekerja', function () {
    $user = User::factory()->create();

    $courseProduct = makeHubProduct([
        'title' => 'Course Alpha',
        'product_type' => ProductType::Course,
    ]);
    makeHubCourse($courseProduct);
    makeHubUserProduct($user, $courseProduct);

    $ebookProduct = makeHubProduct([
        'title' => 'Ebook Beta',
        'slug' => 'ebook-beta-'.uniqid(),
        'product_type' => ProductType::Ebook,
    ]);
    makeHubUserProduct($user, $ebookProduct);

    $this->actingAs($user)
        ->get(route('my-products.index', ['product_type' => ProductType::Course->value]))
        ->assertOk()
        ->assertSee('Course Alpha')
        ->assertDontSee('Ebook Beta');
});

test('filter product_type event bekerja', function () {
    $user = User::factory()->create();

    $eventProduct = makeHubProduct([
        'title' => 'Event Alpha',
        'product_type' => ProductType::Event,
    ]);
    makeHubEvent($eventProduct);
    makeHubUserProduct($user, $eventProduct);

    $bundleProduct = makeHubProduct([
        'title' => 'Bundle Beta',
        'slug' => 'bundle-beta-'.uniqid(),
        'product_type' => ProductType::Bundle,
    ]);
    makeHubUserProduct($user, $bundleProduct);

    $this->actingAs($user)
        ->get(route('my-products.index', ['product_type' => ProductType::Event->value]))
        ->assertOk()
        ->assertSee('Event Alpha')
        ->assertDontSee('Bundle Beta');
});

test('card ebook mengarah ke produk saya detail', function () {
    $user = User::factory()->create();
    $product = makeHubProduct([
        'title' => 'Ebook CTA',
        'product_type' => ProductType::Ebook,
    ]);
    $userProduct = makeHubUserProduct($user, $product);

    ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'File Ebook',
        'file_path' => 'products/files/ebook-cta.pdf',
        'file_type' => 'pdf',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee(route('my-products.show', $userProduct), false)
        ->assertSee('Baca Ebook');
});

test('card course mengarah ke kelas saya detail', function () {
    $user = User::factory()->create();
    $product = makeHubProduct([
        'title' => 'Course CTA',
        'product_type' => ProductType::Course,
    ]);
    makeHubCourse($product);
    $userProduct = makeHubUserProduct($user, $product);

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee(route('my-courses.show', $userProduct), false)
        ->assertSee('Masuk Kelas');
});

test('detail akses course draft tetap menampilkan masuk kelas untuk pemilik entitlement aktif', function () {
    $user = User::factory()->create();
    $product = makeHubProduct([
        'title' => 'Course Draft CTA',
        'product_type' => ProductType::Course,
    ]);
    makeHubCourse($product, [
        'status' => CourseStatus::Draft,
        'published_at' => null,
    ]);
    $userProduct = makeHubUserProduct($user, $product);

    $this->actingAs($user)
        ->get(route('my-products.show', $userProduct))
        ->assertOk()
        ->assertSee('Masuk Kelas')
        ->assertDontSee('Materi kelas sedang disiapkan.');
});

test('card event mengarah ke event saya detail jika registrasi tersedia', function () {
    $user = User::factory()->create();
    $product = makeHubProduct([
        'title' => 'Event CTA',
        'product_type' => ProductType::Event,
    ]);
    $event = makeHubEvent($product);
    $userProduct = makeHubUserProduct($user, $product);
    $registration = EventRegistration::query()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'status' => EventRegistrationStatus::Registered,
        'registered_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee(route('my-events.show', $registration), false)
        ->assertSee('Lihat Event');
});

test('user tanpa produk melihat empty state dan cta marketplace', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee('Belum ada produk yang dimiliki')
        ->assertSee('Produk yang Anda beli akan muncul di sini setelah pembayaran berhasil.')
        ->assertSee(route('marketplace.index'), false);
});

test('sidebar tidak menampilkan kelas saya dan event saya sebagai menu utama', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertDontSee('Kelas Saya')
        ->assertDontSee('Event Saya');
});

test('sidebar menampilkan marketplace dan produk saya', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('my-products.index'))
        ->assertOk()
        ->assertSee('Marketplace')
        ->assertSee('Produk Saya');
});
