<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php($epiChannel = auth()->user()->loadMissing('epiChannel')->epiChannel)
        @php($hasActiveEpiChannel = $epiChannel?->isActive() ?? false)

        <flux:sidebar sticky collapsible class="epic-sidebar border-e border-zinc-800/80 bg-zinc-950 text-white">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <div class="ml-auto flex items-center gap-1">
                    {{-- Theme toggle --}}
                    <button
                        x-data
                        type="button"
                        x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                        x-tooltip.raw="Toggle tema"
                        class="flex size-8 items-center justify-center rounded-lg text-zinc-400 transition-colors duration-150 hover:bg-white/10 hover:text-white"
                        aria-label="Toggle tema terang/gelap"
                    >
                        <svg x-show="$flux.appearance !== 'dark'" viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="12" r="4.25" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 3.75V2.75M12 21.25V20.25M20.25 12H21.25M2.75 12H3.75M17.78 6.22L18.49 5.51M5.51 18.49L6.22 17.78M17.78 17.78L18.49 18.49M5.51 5.51L6.22 6.22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <svg x-show="$flux.appearance === 'dark'" viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display:none">
                            <path d="M20.354 15.354A9 9 0 0 1 8.646 3.646 9.003 9.003 0 0 0 12 21a9.003 9.003 0 0 0 8.354-5.646Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <flux:sidebar.collapse class="lg:hidden" />
                </div>
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('marketplace.index')" :current="request()->routeIs('marketplace.*')" wire:navigate>
                        Marketplace
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="squares-2x2" :href="route('my-products.index')" :current="request()->routeIs('my-products.*') || request()->routeIs('my-courses.*') || request()->routeIs('my-events.*')" wire:navigate>
                        Produk Saya
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('orders.index')" :current="request()->routeIs('orders.*')" wire:navigate>
                        Invoice
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit') || request()->routeIs('settings.*')" wire:navigate>
                        Pengaturan
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="EPI Channel" class="grid">
                    @if ($hasActiveEpiChannel)
                        <flux:sidebar.item icon="chart-bar" :href="route('epi-channel.dashboard')" :current="request()->routeIs('epi-channel.dashboard')" wire:navigate>
                            Dashboard EPIC
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="link" :href="route('epi-channel.links')" :current="request()->routeIs('epi-channel.links')" wire:navigate>
                            Link Promosi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shopping-bag" :href="route('epi-channel.products')" :current="request()->routeIs('epi-channel.products')" wire:navigate>
                            Produk Promosi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cursor-arrow-rays" :href="route('epi-channel.visits')" :current="request()->routeIs('epi-channel.visits')" wire:navigate>
                            Kunjungan
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('epi-channel.orders')" :current="request()->routeIs('epi-channel.orders')" wire:navigate>
                            Referral Order
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('dashboard.clients.index')" :current="request()->routeIs('dashboard.clients.*')" wire:navigate>
                            Klien
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('epi-channel.commissions')" :current="request()->routeIs('epi-channel.commissions')" wire:navigate>
                            Komisi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('epi-channel.payouts')" :current="request()->routeIs('epi-channel.payouts')" wire:navigate>
                            Payout
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="photo" :href="route('epi-channel.promo-assets')" :current="request()->routeIs('epi-channel.promo-assets')" wire:navigate>
                            Materi Promosi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="identification" :href="route('epi-channel.profile')" :current="request()->routeIs('epi-channel.profile')" wire:navigate>
                            Profil Channel
                        </flux:sidebar.item>
                    @else
                        <flux:sidebar.item icon="information-circle" :href="route('epi-channel.dashboard')" :current="request()->routeIs('epi-channel.*')" wire:navigate>
                            Status EPI Channel
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="hidden lg:block px-2 pb-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-[1rem] border border-rose-300/80 bg-[linear-gradient(180deg,#fff1f2_0%,#ffe4e6_100%)] px-4 py-3 text-sm font-semibold uppercase tracking-[0.16em] text-rose-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.95),0_12px_24px_rgba(190,24,93,0.14)] transition duration-200 hover:-translate-y-0.5 hover:border-rose-400 hover:bg-[linear-gradient(180deg,#ffe4e6_0%,#fecdd3_100%)] hover:text-rose-800 hover:shadow-[inset_0_1px_0_rgba(255,255,255,0.95),0_0_0_1px_rgba(244,63,94,0.12),0_16px_28px_rgba(244,63,94,0.2),0_0_22px_rgba(251,113,133,0.16)] active:translate-y-0 active:shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_8px_16px_rgba(190,24,93,0.14)]"
                    >
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 transition duration-200 group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M14 16.5L18.5 12L14 7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18 12H9.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            <path d="M9.75 5.75H7.75C6.64543 5.75 5.75 6.64543 5.75 7.75V16.25C5.75 17.3546 6.64543 18.25 7.75 18.25H9.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            {{-- Theme toggle mobile --}}
            <button
                x-data
                type="button"
                x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                class="mr-1 flex size-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 shadow-sm transition-colors duration-150 hover:bg-zinc-50 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                aria-label="Toggle tema terang/gelap"
            >
                <svg x-show="$flux.appearance !== 'dark'" viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="12" cy="12" r="4.25" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 3.75V2.75M12 21.25V20.25M20.25 12H21.25M2.75 12H3.75M17.78 6.22L18.49 5.51M5.51 18.49L6.22 17.78M17.78 17.78L18.49 18.49M5.51 5.51L6.22 6.22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <svg x-show="$flux.appearance === 'dark'" viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display:none">
                    <path d="M20.354 15.354A9 9 0 0 1 8.646 3.646 9.003 9.003 0 0 0 12 21a9.003 9.003 0 0 0 8.354-5.646Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :avatar="auth()->user()->profile_photo_url"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    :src="auth()->user()->profile_photo_url"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('marketplace.index')" icon="shopping-bag" wire:navigate>
                            Marketplace
                        </flux:menu.item>
                        <flux:menu.item :href="route('my-products.index')" icon="squares-2x2" wire:navigate>
                            Produk Saya
                        </flux:menu.item>
                        <flux:menu.item :href="route('orders.index')" icon="document-text" wire:navigate>
                            Invoice
                        </flux:menu.item>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            Pengaturan
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <x-pwa-install-button />
        @include('partials.pwa-scripts')
        @fluxScripts
    </body>
</html>
