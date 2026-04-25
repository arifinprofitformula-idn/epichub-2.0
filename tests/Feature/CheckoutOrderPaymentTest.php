<?php

use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

test('guests are redirected to login when accessing checkout', function () {
    $product = Product::query()->create([
        'title' => 'Produk A',
        'slug' => 'produk-a',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $this->get(route('checkout.show', $product->slug))
        ->assertRedirect(route('login'));
});

test('authenticated user can create direct order and payment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::query()->create([
        'title' => 'Produk A',
        'slug' => 'produk-a',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $response = $this->post(route('checkout.store', $product->slug));
    $response->assertRedirect();

    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('order_items', 1);
    $this->assertDatabaseCount('payments', 1);

    $order = Order::query()->firstOrFail();
    expect($order->user_id)->toBe($user->id);
    expect($order->status)->toBe(OrderStatus::Unpaid);

    $payment = Payment::query()->firstOrFail();
    expect($payment->order_id)->toBe($order->id);
    expect($payment->status)->toBe(PaymentStatus::Pending);
    expect($payment->payment_method)->toBe(PaymentMethod::ManualBankTransfer);
});

test('user cannot view other users order or payment', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $product = Product::query()->create([
        'title' => 'Produk A',
        'slug' => 'produk-a',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $order = Order::query()->create([
        'user_id' => $owner->id,
        'order_number' => 'ORD-20260101-000001',
        'status' => OrderStatus::Unpaid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $owner->name,
        'customer_email' => $owner->email,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-000001',
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    $this->actingAs($other);

    $this->get(route('orders.show', $order))->assertForbidden();
    $this->get(route('payments.show', $payment))->assertForbidden();
});

test('payment proof upload is limited to payment owner', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $other = User::factory()->create();

    $product = Product::query()->create([
        'title' => 'Produk A',
        'slug' => 'produk-a',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $order = Order::query()->create([
        'user_id' => $owner->id,
        'order_number' => 'ORD-20260101-000001',
        'status' => OrderStatus::Unpaid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $owner->name,
        'customer_email' => $owner->email,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-000001',
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    $file = UploadedFile::fake()->image('proof.jpg')->size(500);

    $this->actingAs($other);
    $this->post(route('payments.proof.store', $payment), ['proof' => $file])->assertForbidden();

    $this->actingAs($owner);
    $this->post(route('payments.proof.store', $payment), ['proof' => $file])->assertRedirect();

    $payment->refresh();
    expect($payment->proof_of_payment)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->proof_of_payment);
});

test('admin can mark payment as paid via action', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();

    $product = Product::query()->create([
        'title' => 'Produk A',
        'slug' => 'produk-a',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $order = Order::query()->create([
        'user_id' => $customer->id,
        'order_number' => 'ORD-20260101-000001',
        'status' => OrderStatus::Unpaid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-000001',
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(PaymentStatus::Success);
    expect($order->status)->toBe(OrderStatus::Paid);
    expect($payment->verified_by)->toBe($admin->id);
    expect($payment->verified_at)->not->toBeNull();
    expect($payment->paid_at)->not->toBeNull();
    expect($order->paid_at)->not->toBeNull();
});

