<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    $originalName = $user->name;

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'hacker@example.com')
        ->set('whatsapp_number', '0812 3456 789')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual($originalName);
    expect($user->email)->not->toEqual('hacker@example.com');
    expect($user->whatsapp_number)->toEqual('628123456789');
    expect($user->email_verified_at)->not->toBeNull();
});

test('whatsapp number can be left empty on profile update', function () {
    $user = User::factory()->create([
        'whatsapp_number' => '628123456789',
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->set('whatsapp_number', '')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->whatsapp_number)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('duplicate whatsapp is rejected on profile update', function () {
    User::factory()->create([
        'whatsapp_number' => '628123456789',
    ]);

    $user = User::factory()->create([
        'whatsapp_number' => '628999999999',
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('whatsapp_number', '+62 812-3456-789')
        ->call('updateProfileInformation');

    $response->assertHasErrors(['whatsapp_number']);

    expect($user->refresh()->whatsapp_number)->toEqual('628999999999');
});

test('profile photo can be uploaded with maximum size 2mb', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user);

    $photo = UploadedFile::fake()->image('avatar.png', 300, 300)->size(1024);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->set('profile_photo', $photo)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('profile photo must not exceed 2mb', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user);

    $photo = UploadedFile::fake()->image('avatar.png')->size(2049);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->set('profile_photo', $photo)
        ->call('updateProfileInformation');

    $response->assertHasErrors(['profile_photo']);

    expect($user->refresh()->profile_photo_path)->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
