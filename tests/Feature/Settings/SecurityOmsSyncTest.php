<?php

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('epic local password change sends outbound oms request', function () {
    config([
        'epichub.oms.enabled' => true,
        'epichub.oms.signature_secret' => 'signature-key',
        'epichub.oms.password_encryption_key' => base64_encode(random_bytes(32)),
        'epichub.oms.outbound_change_password_url' => 'https://oms.test/change-password',
        'epichub.oms.response.success' => '00',
    ]);

    Http::fake([
        'https://oms.test/change-password' => Http::response([
            'response_code' => '00',
            'message' => 'Sukses',
        ], 200),
    ]);

    $user = User::factory()->create([
        'password' => Hash::make('OldPassword!'),
    ]);

    EpiChannel::query()->create([
        'user_id' => $user->id,
        'epic_code' => 'EPIC-SYNC',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test('pages::settings.security')
        ->set('current_password', 'OldPassword!')
        ->set('password', 'NewPassword!123')
        ->set('password_confirmation', 'NewPassword!123')
        ->call('updatePassword')
        ->assertHasNoErrors();

    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        if ($request->url() !== 'https://oms.test/change-password') {
            return false;
        }

        $data = $request->data();

        return isset($data['kode_epic'], $data['email_epic'], $data['encrypted_password'])
            && $data['kode_epic'] === 'EPIC-SYNC'
            && $data['email_epic'] === auth()->user()->email
            && $request->hasHeader('X-OMS-Request-Id')
            && $request->hasHeader('X-OMS-Timestamp')
            && $request->hasHeader('X-OMS-Signature');
    });
});
