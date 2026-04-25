<?php

use App\Actions\Affiliates\ApproveCommissionAction;
use App\Actions\Affiliates\CreateCommissionPayoutAction;
use App\Actions\Affiliates\MarkPayoutPaidAction;
use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\CommissionStatus;
use App\Enums\EpiChannelStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Commission;
use App\Models\EpiChannel;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReferralOrder;
use App\Models\ReferralVisit;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

test('referral visit is tracked from referral redirect route', function () {
    $owner = User::factory()->create();
    EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'REF-TRACK',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $this->get('/r/REF-TRACK?to=/')
        ->assertRedirect('/produk');

    $this->assertDatabaseHas('referral_visits', [
        'referral_code' => 'REF-TRACK',
    ]);
});

test('checkout with referral creates referral order', function () {
    $affiliateOwner = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $affiliateOwner->id,
        'epic_code' => 'REF-CHECKOUT',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $buyer = User::factory()->create();
    $product = createAffiliateProduct('checkout-ref-product');

    $visit = ReferralVisit::query()->create([
        'epi_channel_id' => $channel->id,
        'product_id' => $product->id,
        'referral_code' => $channel->epic_code,
        'landing_url' => route('catalog.products.show', $product->slug),
        'clicked_at' => now(),
    ]);

    $cookiePayload = json_encode([
        'epic_code' => $channel->epic_code,
        'visit_id' => $visit->id,
        'at' => now()->timestamp,
    ]);

    $this->actingAs($buyer)
        ->withCookie('epic_ref', (string) $cookiePayload)
        ->post(route('checkout.store', $product->slug))
        ->assertRedirect();

    $order = Order::query()->latest('id')->firstOrFail();

    $this->assertDatabaseHas('referral_orders', [
        'order_id' => $order->id,
        'epi_channel_id' => $channel->id,
        'status' => 'pending',
    ]);
});

test('paid order creates pending commission for affiliate enabled product', function () {
    $admin = User::factory()->create();
    $buyer = User::factory()->create();
    $affiliateOwner = User::factory()->create();

    $channel = EpiChannel::query()->create([
        'user_id' => $affiliateOwner->id,
        'epic_code' => 'PAID-COMM',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $product = createAffiliateProduct('paid-commission-product');
    [$order, $payment] = createOrderAndPayment($buyer, $product);

    ReferralOrder::query()->create([
        'order_id' => $order->id,
        'epi_channel_id' => $channel->id,
        'buyer_user_id' => $buyer->id,
        'status' => 'pending',
        'attributed_at' => now(),
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);

    $this->assertDatabaseHas('commissions', [
        'order_id' => $order->id,
        'epi_channel_id' => $channel->id,
        'status' => CommissionStatus::Pending->value,
    ]);
});

test('self referral does not create commission', function () {
    $admin = User::factory()->create();
    $owner = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'SELF-REF',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $product = createAffiliateProduct('self-ref-product');
    [$order, $payment] = createOrderAndPayment($owner, $product);

    ReferralOrder::query()->create([
        'order_id' => $order->id,
        'epi_channel_id' => $channel->id,
        'buyer_user_id' => $owner->id,
        'status' => 'pending',
        'attributed_at' => now(),
    ]);

    app(MarkPaymentAsPaidAction::class)->execute($payment, $admin);

    expect(Commission::query()->where('order_id', $order->id)->count())->toBe(0);
});

test('mark payment paid twice does not duplicate commission', function () {
    $admin = User::factory()->create();
    $buyer = User::factory()->create();
    $affiliateOwner = User::factory()->create();

    $channel = EpiChannel::query()->create([
        'user_id' => $affiliateOwner->id,
        'epic_code' => 'DOUBLE-PAID',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $product = createAffiliateProduct('double-paid-product');
    [$order, $payment] = createOrderAndPayment($buyer, $product);

    ReferralOrder::query()->create([
        'order_id' => $order->id,
        'epi_channel_id' => $channel->id,
        'buyer_user_id' => $buyer->id,
        'status' => 'pending',
        'attributed_at' => now(),
    ]);

    $action = app(MarkPaymentAsPaidAction::class);
    $action->execute($payment, $admin);
    $action->execute($payment->fresh(), $admin);

    expect(Commission::query()->where('order_id', $order->id)->count())->toBe(1);
});

test('affiliate dashboard only shows own stats', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create();
    $buyer = User::factory()->create();
    $product = createAffiliateProduct('dashboard-own-product');

    $viewerChannel = EpiChannel::query()->create([
        'user_id' => $viewer->id,
        'epic_code' => 'DASH-OWNER',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $otherChannel = EpiChannel::query()->create([
        'user_id' => $other->id,
        'epic_code' => 'DASH-OTHER',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $ownOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'order_number' => 'ORD-OWN-'.Str::upper(Str::random(6)),
        'status' => OrderStatus::Paid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'paid_at' => now(),
    ]);
    $ownItem = $ownOrder->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    $otherOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'order_number' => 'ORD-OTHER-'.Str::upper(Str::random(6)),
        'status' => OrderStatus::Paid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'paid_at' => now(),
    ]);
    $otherItem = $otherOrder->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    Commission::query()->create([
        'epi_channel_id' => $viewerChannel->id,
        'order_id' => $ownOrder->id,
        'order_item_id' => $ownItem->id,
        'product_id' => $product->id,
        'buyer_user_id' => $buyer->id,
        'commission_type' => 'percentage',
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '10000.00',
        'status' => CommissionStatus::Pending,
    ]);

    Commission::query()->create([
        'epi_channel_id' => $otherChannel->id,
        'order_id' => $otherOrder->id,
        'order_item_id' => $otherItem->id,
        'product_id' => $product->id,
        'buyer_user_id' => $buyer->id,
        'commission_type' => 'percentage',
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '10000.00',
        'status' => CommissionStatus::Pending,
    ]);

    $this->actingAs($viewer)
        ->get(route('epi-channel.commissions'))
        ->assertOk()
        ->assertSee($ownOrder->order_number)
        ->assertDontSee($otherOrder->order_number);
});

test('admin can approve commission', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $affiliateOwner = User::factory()->create();
    $buyer = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $affiliateOwner->id,
        'epic_code' => 'APPROVE-COMM',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = createAffiliateProduct('approve-commission-product');
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'order_number' => 'ORD-APP-'.Str::upper(Str::random(6)),
        'status' => OrderStatus::Paid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'paid_at' => now(),
    ]);
    $item = $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    $commission = Commission::query()->create([
        'epi_channel_id' => $channel->id,
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'product_id' => $product->id,
        'buyer_user_id' => $buyer->id,
        'commission_type' => 'percentage',
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '10000.00',
        'status' => CommissionStatus::Pending,
    ]);

    app(ApproveCommissionAction::class)->execute($commission, $admin);

    expect($commission->fresh()->status)->toBe(CommissionStatus::Approved);
});

test('payout paid changes commissions to paid', function () {
    $admin = User::factory()->create();
    $affiliateOwner = User::factory()->create();
    $buyer = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $affiliateOwner->id,
        'epic_code' => 'PAYOUT-PAID',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = createAffiliateProduct('payout-paid-product');

    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'order_number' => 'ORD-PAY-'.Str::upper(Str::random(6)),
        'status' => OrderStatus::Paid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'paid_at' => now(),
    ]);

    $itemA = $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title,
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);
    $itemB = $order->items()->create([
        'product_id' => $product->id,
        'product_title' => $product->title.' B',
        'product_type' => ProductType::Ebook->value,
        'quantity' => 1,
        'unit_price' => '100000.00',
        'subtotal_amount' => '100000.00',
    ]);

    $commissionA = Commission::query()->create([
        'epi_channel_id' => $channel->id,
        'order_id' => $order->id,
        'order_item_id' => $itemA->id,
        'product_id' => $product->id,
        'buyer_user_id' => $buyer->id,
        'commission_type' => 'percentage',
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '10000.00',
        'status' => CommissionStatus::Approved,
    ]);
    $commissionB = Commission::query()->create([
        'epi_channel_id' => $channel->id,
        'order_id' => $order->id,
        'order_item_id' => $itemB->id,
        'product_id' => $product->id,
        'buyer_user_id' => $buyer->id,
        'commission_type' => 'percentage',
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '10000.00',
        'status' => CommissionStatus::Approved,
    ]);

    $payout = app(CreateCommissionPayoutAction::class)->execute(
        channel: $channel,
        actor: $admin,
        commissionIds: [$commissionA->id, $commissionB->id],
        notes: 'Manual payout',
    );

    app(MarkPayoutPaidAction::class)->execute($payout, $admin);

    expect($payout->fresh()->status)->toBe(\App\Enums\PayoutStatus::Paid);
    expect($commissionA->fresh()->status)->toBe(CommissionStatus::Paid);
    expect($commissionB->fresh()->status)->toBe(CommissionStatus::Paid);
});

function createAffiliateProduct(string $slug): Product
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
        'is_affiliate_enabled' => true,
        'affiliate_commission_type' => 'percentage',
        'affiliate_commission_value' => '10.00',
    ]);
}

/**
 * @return array{Order, Payment}
 */
function createOrderAndPayment(User $buyer, Product $product): array
{
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'order_number' => 'ORD-'.Str::upper(Str::random(8)),
        'status' => OrderStatus::Unpaid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
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
        'payment_number' => 'PAY-'.Str::upper(Str::random(8)),
        'payment_method' => PaymentMethod::ManualBankTransfer,
        'status' => PaymentStatus::Pending,
        'amount' => '100000.00',
        'currency' => 'IDR',
    ]);

    return [$order, $payment];
}
