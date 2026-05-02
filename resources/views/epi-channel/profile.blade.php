<x-layouts::app :title="__('Profil EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="PROFILE"
            description="Informasi profil EPI Channel."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6 space-y-4">
            @if (session('epi_channel_profile_notice'))
                <div class="rounded-[var(--radius-lg)] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('epi_channel_profile_notice') }}
                </div>
            @endif

            {{-- Hero card: nama pengguna --}}
            <div class="relative overflow-hidden rounded-[var(--radius-lg)] bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700 p-6 text-white shadow-lg dark:from-emerald-700 dark:via-emerald-800 dark:to-teal-900">
                <div class="absolute -right-8 -top-8 size-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-6 -left-6 size-32 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-white/20 text-2xl font-bold uppercase shadow-inner overflow-hidden">
                        @if ($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="size-full object-cover">
                        @else
                            {{ mb_substr($user->name ?? $channel->store_name ?? $channel->epic_code, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">Nama Pengguna</div>
                        <div class="mt-0.5 text-xl font-bold leading-tight">{{ $user->name ?: '-' }}</div>
                        <div class="mt-0.5 text-sm text-emerald-200">{{ $user->email }}</div>
                    </div>
                </div>
            </div>

            {{-- Info grid --}}
            <x-ui.card class="p-6">
                <div class="grid gap-4 md:grid-cols-2">

                    {{-- ID EPIC --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-emerald-100 bg-emerald-50 px-4 py-4 dark:border-emerald-900/40 dark:bg-emerald-950/30">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3.75" y="3.75" width="16.5" height="16.5" rx="2.25" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M8 12H16M8 8.5H16M8 15.5H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">ID EPIC</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $channel->epic_code }}</div>
                        </div>
                    </div>

                    {{-- EPI STORE --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-sky-100 bg-sky-50 px-4 py-4 dark:border-sky-900/40 dark:bg-sky-950/30">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M3.75 6.75H20.25L19 14.25H5L3.75 6.75Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <circle cx="9" cy="19" r="1.25" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="16" cy="19" r="1.25" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-600 dark:text-sky-400">EPI STORE</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $channel->store_name ?: '-' }}</div>
                        </div>
                    </div>

                    {{-- ID EPIC Pereferral --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-violet-100 bg-violet-50 px-4 py-4 dark:border-violet-900/40 dark:bg-violet-950/30">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M17 8C17 10.7614 14.7614 13 12 13C9.23858 13 7 10.7614 7 8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8Z" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M3 21C3.95728 17.9237 7.7043 16 12 16C16.2957 16 20.0427 17.9237 21 21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-600 dark:text-violet-400">ID EPIC Pereferral</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $channel->sponsor_epic_code ?: '-' }}</div>
                        </div>
                    </div>

                    {{-- Nama Pereferral --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-rose-100 bg-rose-50 px-4 py-4 dark:border-rose-900/40 dark:bg-rose-950/30">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M1 21C1.92857 18.1429 5.14286 16 9 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M17 14L19 16L23 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600 dark:text-rose-400">Nama Pereferral</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $channel->sponsor_name ?: '-' }}</div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-amber-100 bg-amber-50 px-4 py-4 dark:border-amber-900/40 dark:bg-amber-950/30">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="12" r="9.25" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M12 7V12.5L15 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-400">Status</div>
                            <div class="mt-1">@include('epi-channel.partials.status-badge', ['status' => $channel->status])</div>
                        </div>
                    </div>

                    {{-- Source --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-zinc-100 bg-zinc-50 px-4 py-4 dark:border-zinc-800 dark:bg-zinc-900/40">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 3.75C7.99594 3.75 4.75 6.99594 4.75 11C4.75 15.0041 7.99594 18.25 12 18.25C16.0041 18.25 19.25 15.0041 19.25 11C19.25 6.99594 16.0041 3.75 12 3.75Z" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M12 18.25V20.25M8 20.25H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Source</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $channel->source ?: '-' }}</div>
                        </div>
                    </div>

                    {{-- Activated At - full width --}}
                    <div class="flex items-start gap-4 rounded-[var(--radius-lg)] border border-teal-100 bg-teal-50 px-4 py-4 dark:border-teal-900/40 dark:bg-teal-950/30 md:col-span-2">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3.75" y="4.75" width="16.5" height="16.5" rx="2.25" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M8 2.75V6.25M16 2.75V6.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M3.75 9.25H20.25" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 13H10M14 13H16M8 16.5H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">Activated At</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $channel->activated_at?->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                    </div>

                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Data Rekening Payout</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Data rekening digunakan untuk proses pencairan komisi.
                        </div>
                    </div>

                    @if ($channel->hasCompletePayoutBankInfo())
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            Rekening Lengkap
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            Rekening Belum Lengkap
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('epi-channel.profile.update') }}" class="mt-5 space-y-5">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="payout_bank_name" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Nama Bank</label>
                            <input
                                id="payout_bank_name"
                                type="text"
                                name="payout_bank_name"
                                value="{{ old('payout_bank_name', $channel->payout_bank_name) }}"
                                placeholder="Contoh: BCA"
                                class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >
                            @error('payout_bank_name')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="payout_bank_account_number" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Nomor Rekening</label>
                            <input
                                id="payout_bank_account_number"
                                type="text"
                                name="payout_bank_account_number"
                                value="{{ old('payout_bank_account_number', $channel->payout_bank_account_number) }}"
                                placeholder="Masukkan nomor rekening"
                                class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >
                            @error('payout_bank_account_number')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="payout_bank_account_holder_name" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Nama Pemilik Rekening</label>
                            <input
                                id="payout_bank_account_holder_name"
                                type="text"
                                name="payout_bank_account_holder_name"
                                value="{{ old('payout_bank_account_holder_name', $channel->payout_bank_account_holder_name) }}"
                                placeholder="Nama sesuai rekening"
                                class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >
                            @error('payout_bank_account_holder_name')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 bg-zinc-50 px-4 py-4 dark:border-zinc-800 dark:bg-zinc-900/40">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Preview Tampilan User</div>
                        <div class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                            {{ $channel->payout_bank_name ?: '-' }}
                            ·
                            {{ $channel->maskedPayoutBankAccountNumber() ?: 'Nomor rekening belum diisi' }}
                            ·
                            {{ $channel->payout_bank_account_holder_name ?: '-' }}
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button variant="primary" size="md" type="submit">
                            Simpan Data Rekening
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
