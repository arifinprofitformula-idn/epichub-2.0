<?php

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\OmsIntegrationLog;
use App\Models\User;
use App\Services\Oms\OmsPasswordCipher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    config([
        'epichub.oms.enabled' => true,
        'epichub.oms.signature_secret' => 'test-signature-secret',
        'epichub.oms.password_encryption_key' => base64_encode(random_bytes(32)),
        'epichub.oms.response.success' => '00',
        'epichub.oms.response.failed' => '99',
    ]);
});

test('oms create account creates user and epi channel', function () {
    Role::findOrCreate('customer');
    Role::findOrCreate('affiliate');

    $cipher = app(OmsPasswordCipher::class);
    $payload = [
        'kode_epic' => 'EPIC1001',
        'nama_epic' => 'Epic User',
        'email_epic' => 'epic1001@example.com',
        'no_tlp_epic' => '081234567890',
        'nama_epi_store' => 'Epic Store',
        'sponsor_epic_code' => 'EPIC0001',
        'sponsor_name' => 'Sponsor 1',
        'encrypted_password' => $cipher->encrypt('Secret123!'),
    ];

    $requestId = (string) Str::uuid();
    $response = $this->withHeaders(omsHeaders($payload, $requestId))
        ->postJson('/api/oms/epi-channel/create-account', $payload);

    $response->assertOk()->assertJson([
        'response_code' => '00',
        'message' => 'Sukses',
        'data' => [
            'epic_code' => 'EPIC1001',
            'email' => 'epic1001@example.com',
        ],
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'epic1001@example.com',
        'name' => 'Epic User',
    ]);

    $user = User::query()->where('email', 'epic1001@example.com')->firstOrFail();
    expect(Hash::check('Secret123!', $user->password))->toBeTrue();

    $this->assertDatabaseHas('epi_channels', [
        'user_id' => $user->id,
        'epic_code' => 'EPIC1001',
        'status' => EpiChannelStatus::Active->value,
    ]);
});

test('oms create account resend is idempotent', function () {
    $cipher = app(OmsPasswordCipher::class);

    $payload = [
        'kode_epic' => 'EPIC1002',
        'nama_epic' => 'Epic Resend',
        'email_epic' => 'epic1002@example.com',
        'encrypted_password' => $cipher->encrypt('Secret123!'),
    ];
    $requestId = (string) Str::uuid();
    $headers = omsHeaders($payload, $requestId);

    $this->withHeaders($headers)->postJson('/api/oms/epi-channel/create-account', $payload)->assertOk();
    $this->withHeaders($headers)->postJson('/api/oms/epi-channel/create-account', $payload)->assertOk();

    expect(User::query()->where('email', 'epic1002@example.com')->count())->toBe(1);
    expect(EpiChannel::query()->where('epic_code', 'EPIC1002')->count())->toBe(1);
    expect(OmsIntegrationLog::query()->where('request_id', $requestId)->count())->toBe(1);
});

test('oms create account with same email and different epic code fails', function () {
    $cipher = app(OmsPasswordCipher::class);

    $firstPayload = [
        'kode_epic' => 'EPIC2001',
        'nama_epic' => 'Epic One',
        'email_epic' => 'same-email@example.com',
        'encrypted_password' => $cipher->encrypt('Secret123!'),
    ];

    $this->withHeaders(omsHeaders($firstPayload, (string) Str::uuid()))
        ->postJson('/api/oms/epi-channel/create-account', $firstPayload)
        ->assertOk()
        ->assertJson(['response_code' => '00']);

    $secondPayload = [
        'kode_epic' => 'EPIC2002',
        'nama_epic' => 'Epic Two',
        'email_epic' => 'same-email@example.com',
        'encrypted_password' => $cipher->encrypt('Secret456!'),
    ];

    $this->withHeaders(omsHeaders($secondPayload, (string) Str::uuid()))
        ->postJson('/api/oms/epi-channel/create-account', $secondPayload)
        ->assertOk()
        ->assertJson([
            'response_code' => '99',
            'message' => 'Gagal',
        ]);

    expect(EpiChannel::query()->whereIn('epic_code', ['EPIC2001', 'EPIC2002'])->count())->toBe(1);
});

test('oms change password inbound updates password with strict epic code and email', function () {
    $cipher = app(OmsPasswordCipher::class);
    $user = User::factory()->create([
        'email' => 'strict@example.com',
        'password' => Hash::make('OldPassword!'),
    ]);
    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'STRICT-1',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $invalidPayload = [
        'kode_epic' => 'STRICT-1',
        'email_epic' => 'mismatch@example.com',
        'encrypted_password' => $cipher->encrypt('NewPassword!'),
    ];

    $this->withHeaders(omsHeaders($invalidPayload, (string) Str::uuid()))
        ->postJson('/api/oms/epi-channel/change-password', $invalidPayload)
        ->assertOk()
        ->assertJson(['response_code' => '99']);

    expect(Hash::check('OldPassword!', $user->fresh()->password))->toBeTrue();

    $validPayload = [
        'kode_epic' => 'STRICT-1',
        'email_epic' => 'strict@example.com',
        'encrypted_password' => $cipher->encrypt('NewPassword!'),
    ];

    $this->withHeaders(omsHeaders($validPayload, (string) Str::uuid()))
        ->postJson('/api/oms/epi-channel/change-password', $validPayload)
        ->assertOk()
        ->assertJson(['response_code' => '00']);

    expect(Hash::check('NewPassword!', $user->fresh()->password))->toBeTrue();
});

function omsHeaders(array $payload, string $requestId): array
{
    $timestamp = (string) time();
    $rawBody = json_encode($payload);
    $secret = (string) config('epichub.oms.signature_secret');
    $signature = hash_hmac('sha256', $timestamp.$requestId.$rawBody, $secret);

    return [
        'X-OMS-Request-Id' => $requestId,
        'X-OMS-Timestamp' => $timestamp,
        'X-OMS-Signature' => $signature,
    ];
}
