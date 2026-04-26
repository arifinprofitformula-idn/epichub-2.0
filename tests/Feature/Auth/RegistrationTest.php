<?php

use Laravel\Fortify\Features;
use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'Password!23',
        'password_confirmation' => 'Password!23',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('register page shows referral info when ref is valid', function () {
    $owner = User::factory()->create(['name' => 'Sponsor Register']);

    EpiChannel::query()->create([
        'user_id' => $owner->id,
        'epic_code' => 'REG-REF',
        'store_name' => 'Store Register',
        'status' => EpiChannelStatus::Active,
        'source' => 'oms',
        'activated_at' => now(),
    ]);

    $this->get(route('register', ['ref' => 'REG-REF']))
        ->assertOk()
        ->assertSee('Pendaftaran ini terhubung dengan pereferral Anda.')
        ->assertSee('Sponsor Register')
        ->assertSee('REG-REF')
        ->assertSee('Store Register');
});
