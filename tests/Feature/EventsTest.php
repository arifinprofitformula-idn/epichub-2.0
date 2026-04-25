<?php

use App\Actions\Access\GrantProductAccessAction;
use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\EventRegistrationStatus;
use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Enums\UserProductStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Spatie\Permission\Models\Role;

function makeEventProduct(array $overrides = []): Product
{
    return Product::query()->create(array_merge([
        'title' => 'Product Event',
        'slug' => 'product-event-'.uniqid(),
        'product_type' => ProductType::Event,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ], $overrides));
}

function makePublishedEvent(Product $product, array $overrides = []): Event
{
    return Event::query()->create(array_merge([
        'product_id' => $product->id,
        'title' => 'Event A',
        'slug' => 'event-a-'.uniqid(),
        'status' => EventStatus::Published,
        'published_at' => now(),
        'zoom_url' => 'https://zoom.us/j/123',
        'zoom_meeting_id' => '123',
        'zoom_passcode' => 'abc',
    ], $overrides));
}

function makeEventOrderWithSingleItem(User $user, Product $product): Order
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
        'product_type' => ($product->product_type instanceof \BackedEnum) ? $product->product_type->value : (string) $product->product_type,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    return $order->refresh();
}

test('paid event order creates event_registration', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();
    $product = makeEventProduct();
    $event = makePublishedEvent($product);
    $order = makeEventOrderWithSingleItem($customer, $product);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $customer->id,
        'status' => EventRegistrationStatus::Registered->value,
    ]);
});

test('mark paid twice does not duplicate event_registration', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();
    $product = makeEventProduct();
    makePublishedEvent($product);
    $order = makeEventOrderWithSingleItem($customer, $product);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);
    app(MarkPaymentAsPaidAction::class)->execute($payment->refresh(), $admin);

    $this->assertDatabaseCount('event_registrations', 1);
});

test('user can view own event registration and cannot view another user registration', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $product = makeEventProduct();
    $event = makePublishedEvent($product);

    $userProduct = UserProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    $registration = EventRegistration::query()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'status' => EventRegistrationStatus::Registered,
        'registered_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-events.index'))->assertOk();
    $this->get(route('my-events.show', $registration))->assertOk()->assertSee($event->title);

    $this->actingAs($other);
    $this->get(route('my-events.show', $registration))->assertNotFound();
});

test('cancelled registration cannot join or replay', function () {
    $user = User::factory()->create();
    $product = makeEventProduct();
    $event = makePublishedEvent($product, [
        'replay_url' => 'https://example.com/replay',
        'status' => EventStatus::Completed,
    ]);

    $userProduct = UserProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    $registration = EventRegistration::query()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'status' => EventRegistrationStatus::Cancelled,
        'registered_at' => now(),
        'cancelled_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-events.join', $registration))->assertNotFound();
    $this->get(route('my-events.replay', $registration))->assertNotFound();
});

test('completed event with replay_url allows replay', function () {
    $user = User::factory()->create();
    $product = makeEventProduct();
    $event = makePublishedEvent($product, [
        'status' => EventStatus::Completed,
        'replay_url' => 'https://example.com/replay',
    ]);

    $userProduct = UserProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    $registration = EventRegistration::query()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'user_product_id' => $userProduct->id,
        'status' => EventRegistrationStatus::Registered,
        'registered_at' => now(),
    ]);

    $this->actingAs($user);
    $this->get(route('my-events.replay', $registration))->assertRedirect('https://example.com/replay');
});

test('full event cannot be checked out or registered', function () {
    $customerA = User::factory()->create();
    $customerB = User::factory()->create();

    $product = makeEventProduct();
    $event = makePublishedEvent($product, [
        'quota' => 1,
    ]);

    $userProduct = UserProduct::query()->create([
        'user_id' => $customerA->id,
        'product_id' => $product->id,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    EventRegistration::query()->create([
        'event_id' => $event->id,
        'user_id' => $customerA->id,
        'user_product_id' => $userProduct->id,
        'status' => EventRegistrationStatus::Registered,
        'registered_at' => now(),
    ]);

    $this->expectException(\RuntimeException::class);
    app(\App\Actions\Orders\CreateDirectOrderAction::class)->execute($customerB, $product);
});

test('bundle child event creates registration after mark paid', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();

    $childEventProduct = makeEventProduct(['title' => 'Child Event', 'slug' => 'child-event-'.uniqid()]);
    $childEvent = makePublishedEvent($childEventProduct, ['title' => 'Child Event', 'slug' => 'child-event-'.uniqid()]);

    $bundle = Product::query()->create([
        'title' => 'Bundle Event',
        'slug' => 'bundle-event-'.uniqid(),
        'product_type' => ProductType::Bundle,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $bundle->bundledProducts()->sync([
        $childEventProduct->id => ['sort_order' => 0],
    ]);

    $order = makeEventOrderWithSingleItem($customer, $bundle);

    $payment = $order->payments()->create([
        'payment_number' => 'PAY-20260101-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $childEvent->id,
        'user_id' => $customer->id,
    ]);
});

test('manual grant event product creates event registration', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $customer = User::factory()->create();
    $product = makeEventProduct();
    $event = makePublishedEvent($product);

    $userProduct = app(GrantProductAccessAction::class)->execute(
        user: $customer,
        product: $product,
        order: null,
        orderItem: null,
        sourceProduct: null,
        actor: $admin,
    );

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $customer->id,
        'user_product_id' => $userProduct->id,
    ]);
});

test('public event detail does not expose zoom_url', function () {
    $product = makeEventProduct();
    $event = makePublishedEvent($product, [
        'zoom_url' => 'https://zoom.us/j/secret',
        'zoom_meeting_id' => '999',
        'zoom_passcode' => 'secret',
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertDontSee('https://zoom.us/j/secret')
        ->assertDontSee('999')
        ->assertDontSee('secret');
});

