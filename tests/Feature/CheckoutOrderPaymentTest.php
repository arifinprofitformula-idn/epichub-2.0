<?php

use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\EpiChannelStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\EpiChannel;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReferralVisit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

test('guest can open checkout page', function () {
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
        ->assertOk()
        ->assertSee('Informasi Akun & Kontak')
        ->assertSee('Selesaikan Pesanan');
});

test('guest checkout with new email and whatsapp creates user order and payment', function () {
    Role::findOrCreate('customer');

    $product = Product::query()->create([
        'title' => 'Produk Guest',
        'slug' => 'produk-guest',
        'product_type' => ProductType::Ebook,
        'price' => '125000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $response = $this->post(route('checkout.store', $product->slug), [
        'name' => 'Guest Checkout',
        'email' => 'guest.checkout@example.com',
        'whatsapp_number' => '0812 3456 789',
        'password' => 'Password!23',
        'password_confirmation' => 'Password!23',
    ]);

    $user = User::query()->where('email', 'guest.checkout@example.com')->firstOrFail();
    $payment = Payment::query()->latest('id')->firstOrFail();
    $order = Order::query()->latest('id')->firstOrFail();

    $response->assertRedirect(route('payments.show', $payment));

    expect($user->whatsapp_number)->toEqual('628123456789');
    expect($user->hasRole('customer'))->toBeTrue();
    expect($order->user_id)->toBe($user->id);
    expect($payment->order_id)->toBe($order->id);

    $this->assertAuthenticatedAs($user);
});

test('guest checkout with existing email is rejected and prompted to login', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
        'whatsapp_number' => '628111111111',
    ]);

    $product = createCheckoutProduct('existing-email-product');

    $this->from(route('checkout.show', $product->slug))
        ->post(route('checkout.store', $product->slug), [
            'name' => 'Guest Checkout',
            'email' => 'existing@example.com',
            'whatsapp_number' => '628222222222',
            'password' => 'Password!23',
            'password_confirmation' => 'Password!23',
        ])
        ->assertRedirect(route('checkout.show', $product->slug))
        ->assertSessionHasErrors([
            'email' => 'Email ini sudah terdaftar. Silakan login untuk melanjutkan pembelian dengan akun tersebut.',
        ]);

    $this->assertDatabaseCount('orders', 0);
});

test('guest checkout with existing whatsapp is rejected and prompted to login', function () {
    User::factory()->create([
        'email' => 'existing-whatsapp@example.com',
        'whatsapp_number' => '628123456789',
    ]);

    $product = createCheckoutProduct('existing-whatsapp-product');

    $this->from(route('checkout.show', $product->slug))
        ->post(route('checkout.store', $product->slug), [
            'name' => 'Guest Checkout',
            'email' => 'guest.whatsapp@example.com',
            'whatsapp_number' => '+62 812-3456-789',
            'password' => 'Password!23',
            'password_confirmation' => 'Password!23',
        ])
        ->assertRedirect(route('checkout.show', $product->slug))
        ->assertSessionHasErrors([
            'whatsapp_number' => 'Nomor WhatsApp ini sudah terdaftar. Silakan login menggunakan akun yang terhubung dengan nomor tersebut.',
        ]);

    $this->assertDatabaseCount('orders', 0);
});

test('guest checkout does not create order when email and whatsapp belong to different accounts', function () {
    User::factory()->create([
        'email' => 'existing-email@example.com',
        'whatsapp_number' => '628111111111',
    ]);
    User::factory()->create([
        'email' => 'another@example.com',
        'whatsapp_number' => '628222222222',
    ]);

    $product = createCheckoutProduct('existing-both-product');

    $this->from(route('checkout.show', $product->slug))
        ->post(route('checkout.store', $product->slug), [
            'name' => 'Guest Checkout',
            'email' => 'existing-email@example.com',
            'whatsapp_number' => '628222222222',
            'password' => 'Password!23',
            'password_confirmation' => 'Password!23',
        ])
        ->assertRedirect(route('checkout.show', $product->slug))
        ->assertSessionHasErrors([
            'email' => 'Email atau WhatsApp sudah digunakan oleh akun lain. Silakan login atau hubungi admin.',
            'whatsapp_number' => 'Email atau WhatsApp sudah digunakan oleh akun lain. Silakan login atau hubungi admin.',
        ]);

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseCount('payments', 0);
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

test('authenticated user can view checkout without password field', function () {
    $user = User::factory()->create([
        'whatsapp_number' => '628123456789',
    ]);

    $product = createCheckoutProduct('auth-checkout-product');

    $this->actingAs($user)
        ->get(route('checkout.show', $product->slug))
        ->assertOk()
        ->assertSee('Akun yang digunakan')
        ->assertSee($user->email)
        ->assertDontSee('Password Akun Baru');
});

test('referral info is shown in checkout when ref is valid', function () {
    $owner = User::factory()->create(['name' => 'Sponsor Aktif']);
    $channel = EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'REF-CARD',
        'store_name' => 'Toko Sponsor',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $product = createCheckoutProduct('referral-card-product');

    $this->withSession([
        'epichub_referral' => [
            'epic_code' => $channel->epic_code,
            'visit_id' => 1,
            'at' => now()->timestamp,
        ],
    ])->get(route('checkout.show', $product->slug))
        ->assertOk()
        ->assertSee('Pendaftaran/pembelian ini akan terhubung dengan pereferral:')
        ->assertSee('Sponsor Aktif')
        ->assertSee('REF-CARD')
        ->assertSee('Toko Sponsor');
});

test('referral fallback is shown in checkout when there is no ref', function () {
    $product = createCheckoutProduct('referral-fallback-product');

    $this->get(route('checkout.show', $product->slug))
        ->assertOk()
        ->assertSee('Pendaftaran/pembelian ini akan terhubung dengan pereferral sistem EPIC Hub Official.')
        ->assertSee('EPIC-HOUSE')
        ->assertSee('EPIC Hub Official');
});

test('checkout url with ref can keep referral attribution for created order', function () {
    $sponsor = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $sponsor->id,
        'epic_code' => 'REF-CHECKOUT-URL',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $product = createCheckoutProduct('ref-attr-product', true);

    $this->get(route('checkout.show', ['product' => $product->slug, 'ref' => $channel->epic_code]))
        ->assertOk()
        ->assertCookie('epic_ref');

    $visit = ReferralVisit::query()->latest('id')->firstOrFail();
    $cookiePayload = json_encode([
        'epic_code' => $channel->epic_code,
        'visit_id' => $visit->id,
        'at' => now()->timestamp,
    ]);

    $this->withCookie('epic_ref', (string) $cookiePayload)
        ->post(route('checkout.store', $product->slug), [
            'name' => 'Guest Referral',
            'email' => 'guest.referral@example.com',
            'whatsapp_number' => '081299900000',
            'password' => 'Password!23',
            'password_confirmation' => 'Password!23',
        ]);

    $order = Order::query()->latest('id')->firstOrFail();

    $this->assertDatabaseHas('referral_orders', [
        'order_id' => $order->id,
        'epi_channel_id' => $channel->id,
    ]);
});

test('draft private and hidden products cannot be checked out', function () {
    $draft = Product::query()->create([
        'title' => 'Draft Product',
        'slug' => 'draft-product',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Draft,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
    ]);

    $private = Product::query()->create([
        'title' => 'Private Product',
        'slug' => 'private-product',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Private,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $hidden = Product::query()->create([
        'title' => 'Hidden Product',
        'slug' => 'hidden-product',
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Hidden,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
    ]);

    $this->get(route('checkout.show', $draft->slug))->assertNotFound();
    $this->get(route('checkout.show', $private->slug))->assertNotFound();
    $this->get(route('checkout.show', $hidden->slug))->assertNotFound();
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

function createCheckoutProduct(string $slug, bool $affiliateEnabled = false): Product
{
    return Product::query()->create([
        'title' => Str::headline(str_replace('-', ' ', $slug)),
        'slug' => $slug,
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
        'is_affiliate_enabled' => $affiliateEnabled,
        'affiliate_commission_type' => $affiliateEnabled ? 'percentage' : null,
        'affiliate_commission_value' => $affiliateEnabled ? '10.00' : null,
    ]);
}

