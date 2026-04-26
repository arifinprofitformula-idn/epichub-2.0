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

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Perbarui nama, foto profil, dan kontak WhatsApp Anda.')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-5">

            {{-- Photo Upload Card --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Gradient top bar --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500"></div>

                <div class="p-5">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/40">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M5 19C5 16.2386 8.13401 14 12 14C15.866 14 19 16.2386 19 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Foto Profil</span>
                    </div>

                    <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                        {{-- Avatar preview --}}
                        <div class="relative shrink-0">
                            <div class="size-20 overflow-hidden rounded-2xl ring-4 ring-violet-100 dark:ring-violet-900/40">
                                @if ($this->profilePhotoPreviewUrl)
                                    <img src="{{ $this->profilePhotoPreviewUrl }}" alt="{{ Auth::user()->name }}" class="size-full object-cover" />
                                @else
                                    <div class="flex size-full items-center justify-center bg-gradient-to-br from-violet-500 to-indigo-600 text-xl font-bold text-white">
                                        {{ Auth::user()->initials() }}
                                    </div>
                                @endif
                            </div>
                            {{-- Upload indicator dot --}}
                            <div class="absolute -bottom-1 -right-1 flex size-6 items-center justify-center rounded-full border-2 border-white bg-violet-500 dark:border-zinc-900">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 5.75V18.25M5.75 12H18.25" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Upload field --}}
                        <div class="w-full min-w-0">
                            <label
                                for="profile_photo_input"
                                class="group flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50/80 px-4 py-5 text-center transition-all duration-200 hover:border-violet-300 hover:bg-violet-50/50 dark:border-zinc-700 dark:bg-zinc-800/50 dark:hover:border-violet-600 dark:hover:bg-violet-900/10"
                            >
                                <div class="flex size-9 items-center justify-center rounded-xl bg-zinc-200 transition-colors duration-200 group-hover:bg-violet-100 dark:bg-zinc-700 dark:group-hover:bg-violet-900/40">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-4 text-zinc-500 transition-colors duration-200 group-hover:text-violet-600 dark:text-zinc-400 dark:group-hover:text-violet-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 4.75V14.25M12 4.75L9 7.75M12 4.75L15 7.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M5.75 16.25V18.25C5.75 18.8023 6.19772 19.25 6.75 19.25H17.25C17.8023 19.25 18.25 18.8023 18.25 18.25V16.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <p class="mt-2.5 text-sm font-semibold text-zinc-600 transition-colors group-hover:text-violet-700 dark:text-zinc-400 dark:group-hover:text-violet-400">
                                    Klik untuk upload foto
                                </p>
                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">JPG, PNG, WEBP · Maks 2MB</p>
                            </label>
                            <input
                                id="profile_photo_input"
                                wire:model="profile_photo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="sr-only"
                            />
                            @error('profile_photo')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/><path d="M12 9V12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="15" r="0.75" fill="currentColor"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <div wire:loading wire:target="profile_photo" class="mt-2 flex items-center gap-1.5 text-xs text-violet-600 dark:text-violet-400">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="30 56" stroke-linecap="round"/></svg>
                                Memuat pratinjau...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Fields Card --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 to-cyan-500"></div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    {{-- Name --}}
                    <div class="p-5">
                        <label for="name_input" class="mb-2 flex items-center gap-2">
                            <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-900/40">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M5 19C5 16.2386 8.13401 14 12 14C15.866 14 19 16.2386 19 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Nama Lengkap</span>
                        </label>
                        <input
                            id="name_input"
                            wire:model="name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Masukkan nama lengkap..."
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-sm outline-none transition-all duration-200 placeholder:text-zinc-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-blue-500 dark:focus:bg-zinc-900 dark:focus:ring-blue-900/30"
                        />
                        @error('name')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/><path d="M12 9V12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="15" r="0.75" fill="currentColor"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email (locked) --}}
                    <div class="p-5">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M5 8L12 13L19 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Email</span>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M7.75 10V7.5C7.75 5.42893 9.42893 3.75 11.5 3.75H12.5C14.5711 3.75 16.25 5.42893 16.25 7.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <rect x="4.75" y="10.75" width="14.5" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <circle cx="12" cy="16" r="1.25" fill="currentColor"/>
                                </svg>
                                Dikunci
                            </span>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50/80 px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-800/60">
                            <span class="flex-1 text-sm text-zinc-500 dark:text-zinc-400">{{ Auth::user()->email }}</span>
                        </div>
                        @if ($this->hasUnverifiedEmail)
                            <div class="mt-3 flex flex-col gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-800/40 dark:bg-amber-900/20 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-2">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 9V13M12 16.5V17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        <path d="M4.9 19H19.1C20.3 19 21.1 17.7 20.5 16.6L13.4 4.1C12.8 3 11.2 3 10.6 4.1L3.5 16.6C2.9 17.7 3.7 19 4.9 19Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                    </svg>
                                    <p class="text-xs font-medium text-amber-800 dark:text-amber-300">Email belum diverifikasi.</p>
                                </div>
                                <button type="button" wire:click.prevent="resendVerificationNotification"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 transition-colors hover:bg-amber-50 dark:border-amber-700 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><path d="M19.25 12C19.25 16.0041 16.0041 19.25 12 19.25C7.99594 19.25 4.75 16.0041 4.75 12C4.75 7.99594 7.99594 4.75 12 4.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M16.75 4.75L19.25 7.25L16.75 9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.75 7.25H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Kirim ulang verifikasi
                                </button>
                            </div>
                        @else
                            <p class="mt-2 flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4" fill="currentColor" fill-opacity=".08"/><path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Email tidak dapat diubah dari halaman ini.
                            </p>
                        @endif
                    </div>

                    {{-- WhatsApp --}}
                    <div class="p-5">
                        <label for="whatsapp_input" class="mb-2 flex items-center gap-2">
                            <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-900/40">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 4.75C8.00594 4.75 4.75 8.00594 4.75 12C4.75 13.3769 5.13832 14.666 5.81265 15.7654L4.75 19.25L8.38052 18.2263C9.44352 18.8367 10.6811 19.25 12 19.25C15.9941 19.25 19.25 15.9941 19.25 12C19.25 8.00594 15.9941 4.75 12 4.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                    <path d="M9.5 10.5C9.5 10.5 9.75 11.5 10.5 12.25C11.25 13 12.5 13.5 12.5 13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">WhatsApp</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="flex items-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <span class="text-sm font-semibold text-zinc-400 dark:text-zinc-500">+62</span>
                            </div>
                            <input
                                id="whatsapp_input"
                                wire:model="whatsapp_number"
                                type="text"
                                autocomplete="tel"
                                placeholder="628123456789"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-sm outline-none transition-all duration-200 placeholder:text-zinc-300 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-emerald-500 dark:focus:bg-zinc-900 dark:focus:ring-emerald-900/30"
                            />
                        </div>
                        <p class="mt-2 flex items-start gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                            <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/><path d="M12 11V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="8.5" r="0.75" fill="currentColor"/></svg>
                            Digunakan sebagai kontak jika kamu aktif sebagai EPI Channel.
                        </p>
                        @error('whatsapp_number')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/><path d="M12 9V12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="15" r="0.75" fill="currentColor"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <button
                type="submit"
                class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-6 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-md shadow-violet-200 transition-all duration-200 hover:from-violet-700 hover:to-indigo-700 hover:shadow-lg hover:shadow-violet-200 active:scale-[0.98] dark:shadow-violet-900/30"
                data-test="update-profile-button"
            >
                <div wire:loading wire:target="updateProfileInformation" class="absolute inset-0 flex items-center justify-center bg-violet-700">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="30 56" stroke-linecap="round"/>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="updateProfileInformation" class="inline-flex items-center justify-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5.75 8.75H15.25C16.3546 8.75 17.25 9.64543 17.25 10.75V18.25C17.25 19.3546 16.3546 20.25 15.25 20.25H8.75C7.64543 20.25 6.75 19.3546 6.75 18.25V8.75H5.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M9.75 8.75V5.75C9.75 4.64543 10.6454 3.75 11.75 3.75H14.5L18.25 7.5V17.25" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M9.75 15.75L11.25 17.25L14.25 13.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="leading-none">SIMPAN PERUBAHAN</span>
                </div>
            </button>
        </form>

        {{-- Delete Account Section --}}
        @if ($this->showDeleteUser)
            <div class="mt-2 overflow-hidden rounded-2xl border border-rose-100 bg-rose-50/50 dark:border-rose-900/40 dark:bg-rose-950/10">
                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/40">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-rose-600 dark:text-rose-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.75 7.75L5.59115 17.4233C5.68102 18.4568 6.54612 19.25 7.58318 19.25H16.4168C17.4539 19.25 18.319 18.4568 18.4088 17.4233L19.25 7.75H4.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M9.75 10.75V16.25M14.25 10.75V16.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M8.75 7.75V6.75C8.75 5.64543 9.64543 4.75 10.75 4.75H13.25C14.3546 4.75 15.25 5.64543 15.25 6.75V7.75" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M3.75 7.75H20.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-bold text-rose-800 dark:text-rose-300">Hapus Akun</div>
                            <p class="mt-1 text-xs text-rose-600/80 dark:text-rose-400/80">Tindakan ini permanen dan tidak dapat dibatalkan. Semua data akun akan dihapus.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <livewire:pages::settings.delete-user-form />
                    </div>
                </div>
            </div>
        @endif
    </x-pages::settings.layout>
</section>
