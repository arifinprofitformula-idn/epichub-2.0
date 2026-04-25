<?php

use App\Enums\AccessLogAction;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\AccessLog;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Facades\Storage;

function makeDeliveryProduct(array $overrides = []): Product
{
    return Product::query()->create(array_merge([
        'title' => 'Produk Delivery',
        'slug' => 'produk-delivery-'.uniqid(),
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ], $overrides));
}

function makeActiveUserProduct(User $user, Product $product, array $overrides = []): UserProduct
{
    return UserProduct::query()->create(array_merge([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ], $overrides));
}

test('user with active entitlement can view delivery page for ebook/digital_file', function () {
    $user = User::factory()->create();
    $product = makeDeliveryProduct(['product_type' => ProductType::Ebook]);
    $userProduct = makeActiveUserProduct($user, $product);

    ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Ebook PDF',
        'file_path' => 'products/files/ebook.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $this->get(route('my-products.show', $userProduct))
        ->assertOk()
        ->assertSee('Ebook PDF');
});

test('user without entitlement cannot view/download/open another user product file and is logged as access_denied', function () {
    Storage::fake('local');
    Storage::fake('public');

    $owner = User::factory()->create();
    $other = User::factory()->create();

    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($owner, $product);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Private PDF',
        'file_path' => 'products/files/private.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Storage::disk('local')->put($file->file_path, 'pdf');

    $this->actingAs($other);
    $this->get(route('my-products.files.download', [$userProduct, $file]))->assertNotFound();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $other->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::AccessDenied->value,
    ]);
});

test('revoked entitlement cannot view/download/open and is logged as access_denied', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product, [
        'status' => UserProductStatus::Revoked,
        'revoked_at' => now(),
    ]);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Revoked PDF',
        'file_path' => 'products/files/revoked.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Storage::disk('local')->put($file->file_path, 'pdf');

    $this->actingAs($user);
    $this->get(route('my-products.files.view', [$userProduct, $file]))->assertNotFound();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::AccessDenied->value,
    ]);
});

test('expired entitlement cannot download and is logged as access_denied', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product, [
        'status' => UserProductStatus::Active,
        'expires_at' => now()->subDay(),
    ]);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Expired PDF',
        'file_path' => 'products/files/expired.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Storage::disk('local')->put($file->file_path, 'pdf');

    $this->actingAs($user);
    $this->get(route('my-products.files.download', [$userProduct, $file]))->assertNotFound();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::AccessDenied->value,
    ]);
});

test('inactive product_file cannot be accessed and is logged as access_denied', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Inactive PDF',
        'file_path' => 'products/files/inactive.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => false,
    ]);

    Storage::disk('local')->put('products/files/inactive.pdf', 'pdf');

    $this->actingAs($user);
    $this->get(route('my-products.files.download', [$userProduct, $file]))->assertNotFound();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::AccessDenied->value,
    ]);
});

test('download creates file_downloaded log and works for file on local disk', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Local PDF',
        'file_path' => 'products/files/local.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Storage::disk('local')->put($file->file_path, 'pdf');

    $this->actingAs($user);
    $this->get(route('my-products.files.download', [$userProduct, $file]))->assertOk();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::FileDownloaded->value,
    ]);
});

test('view creates file_viewed log for viewable mime', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'View PDF',
        'file_path' => 'products/files/view.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Storage::disk('local')->put($file->file_path, 'pdf');

    $this->actingAs($user);
    $this->get(route('my-products.files.view', [$userProduct, $file]))->assertOk();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::FileViewed->value,
    ]);
});

test('open external redirects and creates external_link_opened log', function () {
    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'External Link',
        'external_url' => 'https://example.com/file',
        'file_type' => 'link',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $this->get(route('my-products.files.open', [$userProduct, $file]))->assertRedirect('https://example.com/file');

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::ExternalLinkOpened->value,
    ]);
});

test('bundle page shows child products and file summary', function () {
    $user = User::factory()->create();

    $bundle = makeDeliveryProduct([
        'title' => 'Bundle 1',
        'product_type' => ProductType::Bundle,
        'slug' => 'bundle-'.uniqid(),
    ]);

    $child = makeDeliveryProduct([
        'title' => 'Child A',
        'product_type' => ProductType::Ebook,
        'slug' => 'child-a-'.uniqid(),
    ]);

    ProductFile::query()->create([
        'product_id' => $child->id,
        'title' => 'Child File',
        'file_path' => 'products/files/child.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $bundleUserProduct = makeActiveUserProduct($user, $bundle);
    makeActiveUserProduct($user, $child, ['source_product_id' => $bundle->id]);

    $this->actingAs($user);
    $this->get(route('my-products.show', $bundleUserProduct))
        ->assertOk()
        ->assertSee('Child A')
        ->assertSee('Child File');
});

test('legacy file on public disk can still be downloaded via protected controller', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();
    $product = makeDeliveryProduct();
    $userProduct = makeActiveUserProduct($user, $product);

    $file = ProductFile::query()->create([
        'product_id' => $product->id,
        'title' => 'Legacy PDF',
        'file_path' => 'products/files/legacy.pdf',
        'file_type' => 'pdf',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Storage::disk('public')->put($file->file_path, 'pdf');

    $this->actingAs($user);
    $this->get(route('my-products.files.download', [$userProduct, $file]))->assertOk();

    $this->assertDatabaseHas('access_logs', [
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'action' => AccessLogAction::FileDownloaded->value,
    ]);
});

