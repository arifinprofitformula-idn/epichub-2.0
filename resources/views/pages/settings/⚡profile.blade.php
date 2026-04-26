<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component
{
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $whatsapp_number = '';

    public $profile_photo = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->whatsapp_number = Auth::user()->whatsapp_number ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => $this->nameRules(),
            'whatsapp_number' => $this->whatsappNumberRules(),
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['whatsapp_number'] = $user->normalizedWhatsappNumber($validated['whatsapp_number'] ?? null);

        if ($this->profile_photo !== null) {
            $newProfilePhotoPath = $this->profile_photo->store('profile-photos', 'public');

            if (filled($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $newProfilePhotoPath;
        }

        unset($validated['profile_photo']);

        $user->fill($validated);
        $user->save();
        $this->profile_photo = null;

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    #[Computed]
    public function profilePhotoPreviewUrl(): ?string
    {
        if ($this->profile_photo !== null) {
            return $this->profile_photo->temporaryUrl();
        }

        return Auth::user()?->profile_photo_url;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Perbarui nama, foto profil, dan kontak WhatsApp Anda. Email akun dikunci dan tidak dapat diubah.')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="space-y-4 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-zinc-700 dark:bg-zinc-900/60">
                <div class="flex items-start gap-4">
                    <flux:avatar
                        :name="Auth::user()->name"
                        :initials="Auth::user()->initials()"
                        :src="$this->profilePhotoPreviewUrl"
                        size="xl"
                    />

                    <div class="min-w-0 flex-1 space-y-3">
                        <div>
                            <flux:heading size="lg">Foto Profil</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-500">
                                Upload foto JPG, PNG, atau WEBP dengan ukuran maksimal 2MB.
                            </flux:text>
                        </div>

                        <div>
                            <input
                                wire:model="profile_photo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="block w-full rounded-[1rem] border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-700 file:me-4 file:rounded-full file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:file:bg-white dark:file:text-zinc-900 dark:hover:file:bg-zinc-200"
                            />
                            @error('profile_photo')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                            <div wire:loading wire:target="profile_photo" class="mt-2 text-sm text-zinc-500">
                                Mengunggah pratinjau foto...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" readonly disabled autocomplete="email" />
                <flux:text class="mt-2 text-sm text-zinc-500">
                    Email akun dikunci oleh sistem dan tidak bisa diubah dari halaman profil.
                </flux:text>

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                    </div>
                @endif
            </div>

            <div>
                <flux:input
                    wire:model="whatsapp_number"
                    :label="'Nomor WhatsApp'"
                    type="text"
                    autocomplete="tel"
                    placeholder="628123456789"
                />
                <flux:text class="mt-2 text-sm text-zinc-500">
                    Nomor ini digunakan sebagai kontak pereferral jika Anda aktif sebagai EPI Channel.
                </flux:text>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
