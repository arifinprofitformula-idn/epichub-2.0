<?php

use App\Actions\Access\GrantOrderAccessAction;
use App\Actions\Access\GrantProductAccessAction;
use App\Actions\Access\RevokeProductAccessAction;
use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\AccessLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Spatie\Permission\Models\Role;

function makePublishedProduct(array $overrides = []): Product
{
    return Product::query()->create(array_merge([
        'title' => 'Produk A',
        'slug' => 'produk-a-'.uniqid(),
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ], $overrides));
}

function makeOrderWithSingleItem(User $user, Product $product): Order
{
    $order = Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-20260101-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'status' => OrderStatus::Unpaid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $user->name,
        'customer_email' => $user->email,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ($product->product_type instanceof BackedEnum) ? $product->product_type->value : (string) $product->product_type,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    return $order->refresh();
}

test('unpaid order does not grant access', function () {
    $user = User::factory()->create();
    $product = makePublishedProduct();
    $order = makeOrderWithSingleItem($user, $product);

    $this->expectException(RuntimeException::class);
    app(GrantOrderAccessAction::class)->execute($order);
});

test('mark paid creates user_product and access_logs', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();
    $product = makePublishedProduct();
    $order = makeOrderWithSingleItem($customer, $product);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);

    $this->assertDatabaseCount('user_products', 1);
    $this->assertDatabaseCount('access_logs', 1);

    $userProduct = UserProduct::query()->firstOrFail();
    expect($userProduct->user_id)->toBe($customer->id);
    expect($userProduct->product_id)->toBe($product->id);
    expect($userProduct->order_id)->toBe($order->id);
    expect($userProduct->status)->toBe(UserProductStatus::Active);

    $log = AccessLog::query()->firstOrFail();
    expect($log->user_product_id)->toBe($userProduct->id);
});

test('mark paid can be called twice and does not duplicate entitlements', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();
    $product = makePublishedProduct();
    $order = makeOrderWithSingleItem($customer, $product);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);
    app(MarkPaymentAsPaidAction::class)->execute($payment->refresh(), $admin);

    $this->assertDatabaseCount('user_products', 1);
});

test('user can view /produk-saya and cannot view another user_product', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $product = makePublishedProduct();

    $userProduct = UserProduct::query()->create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
        'order_id' => null,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    $this->actingAs($owner);
    $this->get(route('my-products.index'))->assertOk();
    $this->get(route('my-products.show', $userProduct))->assertOk();

    $this->actingAs($other);
    $this->get(route('my-products.show', $userProduct))->assertForbidden();
});

test('bundle grants bundle and child products entitlements', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();

    $childA = makePublishedProduct(['title' => 'Child A', 'product_type' => ProductType::Ebook]);
    $childB = makePublishedProduct(['title' => 'Child B', 'product_type' => ProductType::Course]);

    $bundle = makePublishedProduct([
        'title' => 'Bundle 1',
        'product_type' => ProductType::Bundle,
        'slug' => 'bundle-'.uniqid(),
    ]);

    $bundle->bundledProducts()->sync([
        $childA->id => ['sort_order' => 0],
        $childB->id => ['sort_order' => 1],
    ]);

    $order = makeOrderWithSingleItem($customer, $bundle);

    $order->update(['status' => OrderStatus::Paid, 'paid_at' => now()]);

    app(GrantOrderAccessAction::class)->execute($order, $admin);

    $this->assertDatabaseCount('user_products', 3);

    $entitlements = UserProduct::query()->where('user_id', $customer->id)->get();
    expect($entitlements->where('product_id', $bundle->id)->count())->toBe(1);
    expect($entitlements->where('product_id', $childA->id)->count())->toBe(1);
    expect($entitlements->where('product_id', $childB->id)->count())->toBe(1);
});

test('manual grant does not duplicate active access and revoke hides from active scope', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();
    $product = makePublishedProduct();

    $action = app(GrantProductAccessAction::class);

    $first = $action->execute(
        user: $customer,
        product: $product,
        actor: $admin,
    );

    $second = $action->execute(
        user: $customer,
        product: $product,
        actor: $admin,
    );

    expect($second->id)->toBe($first->id);
    $this->assertDatabaseCount('user_products', 1);

    app(RevokeProductAccessAction::class)->execute($first, $admin, 'Test revoke');

    $activeCount = UserProduct::query()->where('user_id', $customer->id)->active()->count();
    expect($activeCount)->toBe(0);
});

