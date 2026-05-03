<?php

use App\Enums\AffiliateCommissionType;
use App\Enums\CommissionStatus;
use App\Enums\EpiChannelStatus;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\UserProductStatus;
use App\Enums\ProductVisibility;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EpiChannel;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralVisit;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Str;

test('non-affiliate membuka epi-channel melihat inactive state', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Buka Akses Anda ke Ekosistem EPI Channel')
        ->assertSee('Kontak pereferral belum tersedia');
});

test('non-epi user dengan sponsor yang punya whatsapp melihat tombol whatsapp', function () {
    $sponsorUser = User::factory()->create([
        'name' => 'Sponsor Hebat',
        'whatsapp_number' => '0812 3456 789',
    ]);

    EpiChannel::query()->create([
        'user_id' => $sponsorUser->id,
        'epic_code' => 'SPONSOR-001',
        'store_name' => 'Store Sponsor',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $user = User::factory()->create();

    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'LEAD-001',
        'store_name' => 'Store Lead',
        'sponsor_epic_code' => 'SPONSOR-001',
        'sponsor_name' => 'Sponsor Hebat',
        'status' => EpiChannelStatus::Prospect,
        'source' => 'oms',
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Hubungi Pereferral via WhatsApp')
        ->assertSee('Sponsor Hebat')
        ->assertSee('SPONSOR-001')
        ->assertSee('https://wa.me/628123456789', false);
});

test('non-epi user dengan sponsor tanpa whatsapp melihat fallback', function () {
    $sponsorUser = User::factory()->create([
        'name' => 'Sponsor Tanpa WA',
        'whatsapp_number' => null,
    ]);

    EpiChannel::query()->create([
        'user_id' => $sponsorUser->id,
        'epic_code' => 'SPONSOR-002',
        'store_name' => 'Store Sponsor 2',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $user = User::factory()->create();

    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'LEAD-002',
        'store_name' => 'Store Lead 2',
        'sponsor_epic_code' => 'SPONSOR-002',
        'sponsor_name' => 'Sponsor Tanpa WA',
        'status' => EpiChannelStatus::Inactive,
        'source' => 'oms',
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Kontak pereferral belum tersedia')
        ->assertDontSee('Hubungi Pereferral via WhatsApp');
});

test('non-epi user tanpa sponsor melihat fallback', function () {
    $user = User::factory()->create();

    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'LEAD-003',
        'store_name' => 'Store Lead 3',
        'status' => EpiChannelStatus::Qualified,
        'source' => 'oms',
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Kontak pereferral belum tersedia')
        ->assertDontSee('Hubungi Pereferral via WhatsApp');
});

test('epi channel dashboard tidak memakai env whatsapp support', function () {
    $previousWhatsapp = getenv('EPICHUB_EPI_SUPPORT_WHATSAPP');
    $previousName = getenv('EPICHUB_EPI_SUPPORT_NAME');

    putenv('EPICHUB_EPI_SUPPORT_WHATSAPP=628111111111');
    putenv('EPICHUB_EPI_SUPPORT_NAME=Support Env');

    $user = User::factory()->create();

    try {
        $this->actingAs($user)
            ->get(route('epi-channel.dashboard'))
            ->assertOk()
            ->assertSee('Kontak pereferral belum tersedia')
            ->assertDontSee('628111111111')
            ->assertDontSee('Support Env');
    } finally {
        putenv($previousWhatsapp === false ? 'EPICHUB_EPI_SUPPORT_WHATSAPP' : "EPICHUB_EPI_SUPPORT_WHATSAPP={$previousWhatsapp}");
        putenv($previousName === false ? 'EPICHUB_EPI_SUPPORT_NAME' : "EPICHUB_EPI_SUPPORT_NAME={$previousName}");
    }
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

test('active epi channel dengan whatsapp kosong melihat reminder lengkapi whatsapp', function () {
    $user = User::factory()->create([
        'whatsapp_number' => null,
    ]);

    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-WA-REMINDER',
        'store_name' => 'Channel Reminder',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Lengkapi nomor WhatsApp Anda')
        ->assertSee(route('profile.edit'), false);
});

test('active epi channel tetap melihat dashboard aktif bukan inactive state', function () {
    $user = User::factory()->create([
        'whatsapp_number' => '628123456789',
    ]);

    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-ACTIVE-001',
        'store_name' => 'Channel Aktif Sekali',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard EPI Channel')
        ->assertDontSee('EPI Channel Belum Aktif');
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
        ->assertSee(route('offer.show', $product->slug).'?ref='.$channel->epic_code, false);
});

test('links page mengganti tombol beli sekarang menjadi akses produk jika user sudah punya akses', function () {
    $user = User::factory()->create();
    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-LINK-003',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);
    $product = epiDashboardTestCreateAffiliateProduct('epi-links-owned-product');
    $userProduct = UserProduct::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'access_type' => $product->access_type,
        'status' => UserProductStatus::Active,
        'granted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.links'))
        ->assertOk()
        ->assertSee('Akses Produk')
        ->assertSee(route('my-products.show', $userProduct), false)
        ->assertDontSee('Beli Sekarang');
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

test('visits page menampilkan domisili dan label device yang ringkas', function () {
    $user = User::factory()->create();
    $product = epiDashboardTestCreateAffiliateProduct('epi-visits-device-product');
    $channel = EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPI-VISIT-DEVICE',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    ReferralVisit::query()->create([
        'epi_channel_id' => $channel->id,
        'product_id' => $product->id,
        'referral_code' => $channel->epic_code,
        'landing_url' => 'https://example.test/landing-device',
        'source_url' => 'https://google.com/search?q=epi',
        'visitor_id' => 'visitor-device',
        'session_id' => 'session-device',
        'ip_address' => '103.10.10.10',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148',
        'clicked_at' => now(),
        'metadata' => [
            'city' => 'Bandung',
            'region' => 'Jawa Barat',
            'country' => 'Indonesia',
        ],
    ]);

    $this->actingAs($user)
        ->get(route('epi-channel.visits'))
        ->assertOk()
        ->assertSee('Domisili')
        ->assertSee('Bandung, Jawa Barat, Indonesia')
        ->assertSee('Mobile');
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
