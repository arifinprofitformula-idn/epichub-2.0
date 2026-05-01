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
        'whatsapp_number' => '0812 3456 789',
        'password' => 'Password!23',
        'password_confirmation' => 'Password!23',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'whatsapp_number' => '628123456789',
        'referral_source' => 'default_system',
    ]);
});

test('registration rejects duplicate email', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'whatsapp_number' => '0812 9999 0000',
        'password' => 'Password!23',
        'password_confirmation' => 'Password!23',
    ]);

    $response
        ->assertSessionHasErrors(['email'])
        ->assertRedirect(route('register'));

    $this->assertGuest();
});

test('registration rejects duplicate whatsapp even with different formatting', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
        'whatsapp_number' => '628123456789',
    ]);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'fresh@example.com',
        'whatsapp_number' => '+62 812-3456-789',
        'password' => 'Password!23',
        'password_confirmation' => 'Password!23',
    ]);

    $response
        ->assertSessionHasErrors(['whatsapp_number'])
        ->assertRedirect(route('register'));

    $this->assertGuest();
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
        ->assertSee('Anda diperkenalkan oleh')
        ->assertSee('Sponsor Register');
});

test('register page shows house referral info when there is no ref', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Anda diperkenalkan oleh')
        ->assertSee('EPIC Hub Official');
});
