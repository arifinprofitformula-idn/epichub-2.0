<?php

use App\Enums\AffiliateCommissionType;
use App\Enums\CommissionStatus;
use App\Enums\EpiChannelStatus;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EpiChannel;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralVisit;
use App\Models\User;
use Illuminate\Support\Str;

test('non-affiliate membuka epi-channel melihat inactive state', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Status EPI Channel Anda belum aktif. Aktivasi dilakukan melalui OMS/Admin.');
});

test('non-affiliate membuka commissions diarahkan ke dashboard epi-channel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('epi-channel.commissions'))
        ->assertRedirect(route('epi-channel.dashboard'));
});

test('active epi channel bisa membuka dashboard', function () {
    $user = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-DASH-001',
        'store_name' => 'Channel Aktif',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee($channel->epic_code)
        ->assertSee('Dashboard EPI Channel');
});

test('active epi channel hanya melihat komisi miliknya', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create();
    $product = epiDashboardTestCreateAffiliateProduct('epi-dashboard-commission-scope');

    $viewerChannel = EpiChannel::query()->create([
        'user_id' => $viewer->id,
        'epic_code' => 'EPI-COMM-OWN',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $otherChannel = EpiChannel::query()->create([
        'user_id' => $other->id,
        'epic_code' => 'EPI-COMM-OTHER',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    Commission::query()->create([
        'epi_channel_id' => $viewerChannel->id,
        'order_id' => epiDashboardTestCreatePaidOrder($viewer)->id,
        'product_id' => $product->id,
        'commission_type' => AffiliateCommissionType::Percentage,
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '10000.00',
        'status' => CommissionStatus::Pending,
    ]);

    Commission::query()->create([
        'epi_channel_id' => $otherChannel->id,
        'order_id' => epiDashboardTestCreatePaidOrder($other)->id,
        'product_id' => $product->id,
        'commission_type' => AffiliateCommissionType::Percentage,
        'commission_value' => '10.00',
        'base_amount' => '100000.00',
        'commission_amount' => '20000.00',
        'status' => CommissionStatus::Pending,
    ]);

    $this->actingAs($viewer)
        ->get(route('epi-channel.commissions'))
        ->assertOk()
        ->assertSee('10.000')
        ->assertDontSee('20.000');
});

test('links page menampilkan product referral link', function () {
    $user = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-LINK-001',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = epiDashboardTestCreateAffiliateProduct('epi-links-product');

    $this->actingAs($user)
        ->get(route('epi-channel.links'))
        ->assertOk()
        ->assertSee(route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code, false);
});

test('links page menampilkan landing page affiliate link jika landing page enabled', function () {
    $user = User::factory()->create();
    $channel = EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-LINK-002',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = epiDashboardTestCreateAffiliateProduct('epi-links-landing-product', [
        'landing_page_enabled' => true,
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.links'))
        ->assertOk()
        ->assertSee(route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code]), false);
});

test('payout page hanya menampilkan payout miliknya', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create();

    $viewerChannel = EpiChannel::query()->create([
        'user_id' => $viewer->id,
        'epic_code' => 'EPI-PAYOUT-OWN',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $otherChannel = EpiChannel::query()->create([
        'user_id' => $other->id,
        'epic_code' => 'EPI-PAYOUT-OTHER',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $ownPayout = CommissionPayout::query()->create([
        'epi_channel_id' => $viewerChannel->id,
        'payout_number' => 'PAYOUT-OWN-001',
        'total_amount' => '150000.00',
        'status' => PayoutStatus::Paid,
        'paid_at' => now(),
    ]);

    CommissionPayout::query()->create([
        'epi_channel_id' => $otherChannel->id,
        'payout_number' => 'PAYOUT-OTHER-001',
        'total_amount' => '250000.00',
        'status' => PayoutStatus::Paid,
        'paid_at' => now(),
    ]);

    $this->actingAs($viewer)
        ->get(route('epi-channel.payouts'))
        ->assertOk()
        ->assertSee($ownPayout->payout_number)
        ->assertDontSee('PAYOUT-OTHER-001');
});

test('visits page hanya menampilkan visits miliknya', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create();
    $product = epiDashboardTestCreateAffiliateProduct('epi-visits-product');

    $viewerChannel = EpiChannel::query()->create([
        'user_id' => $viewer->id,
        'epic_code' => 'EPI-VISIT-OWN',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $otherChannel = EpiChannel::query()->create([
        'user_id' => $other->id,
        'epic_code' => 'EPI-VISIT-OTHER',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    ReferralVisit::query()->create([
        'epi_channel_id' => $viewerChannel->id,
        'product_id' => $product->id,
        'referral_code' => $viewerChannel->epic_code,
        'landing_url' => 'https://example.test/landing-own',
        'source_url' => 'https://example.test/source-own',
        'visitor_id' => 'visitor-own',
        'session_id' => 'session-own',
        'user_agent' => 'Mozilla/5.0 own',
        'clicked_at' => now(),
    ]);

    ReferralVisit::query()->create([
        'epi_channel_id' => $otherChannel->id,
        'product_id' => $product->id,
        'referral_code' => $otherChannel->epic_code,
        'landing_url' => 'https://example.test/landing-other',
        'source_url' => 'https://example.test/source-other',
        'visitor_id' => 'visitor-other',
        'session_id' => 'session-other',
        'user_agent' => 'Mozilla/5.0 other',
        'clicked_at' => now(),
    ]);

    $this->actingAs($viewer)
        ->get(route('epi-channel.visits'))
        ->assertOk()
        ->assertSee('landing-own')
        ->assertDontSee('landing-other');
});

function epiDashboardTestCreateAffiliateProduct(string $slug, array $overrides = []): Product
{
    return Product::query()->create(array_merge([
        'title' => Str::headline(str_replace('-', ' ', $slug)),
        'slug' => $slug,
        'product_type' => ProductType::Ebook,
        'price' => '100000.00',
        'status' => ProductStatus::Published,
        'visibility' => ProductVisibility::Public,
        'access_type' => ProductAccessType::InstantAccess,
        'publish_at' => now(),
        'is_affiliate_enabled' => true,
        'landing_page_enabled' => false,
        'affiliate_commission_type' => AffiliateCommissionType::Percentage,
        'affiliate_commission_value' => '10.00',
    ], $overrides));
}

function epiDashboardTestCreatePaidOrder(User $user): Order
{
    return Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::upper(Str::random(8)),
        'status' => OrderStatus::Paid,
        'subtotal_amount' => '100000.00',
        'discount_amount' => '0.00',
        'total_amount' => '100000.00',
        'currency' => 'IDR',
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'paid_at' => now(),
    ]);
}
