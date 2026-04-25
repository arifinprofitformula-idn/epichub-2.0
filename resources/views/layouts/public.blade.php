<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-zinc-950 dark:to-zinc-900">
        <header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/70 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/60">
            <div class="mx-auto flex max-w-[var(--container-5xl)] items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                    <x-app-logo-icon class="size-7 fill-current" />
                    <span class="text-sm tracking-tight">EPIC Hub Premium</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm text-zinc-700 dark:text-zinc-200 md:flex">
                    <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-white">Home</a>
                    <a href="{{ route('catalog.products.index') }}" class="hover:text-zinc-900 dark:hover:text-white">Produk</a>
                    <a href="{{ route('home') }}#event" class="hover:text-zinc-900 dark:hover:text-white">Event</a>
                    <a href="{{ route('home') }}#membership" class="hover:text-zinc-900 dark:hover:text-white">Membership</a>
                    <a href="{{ route('home') }}#epi-channel" class="hover:text-zinc-900 dark:hover:text-white">EPI Channel</a>
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <x-ui.button variant="ghost" size="sm" :href="route('dashboard')">
                            Dashboard
                        </x-ui.button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button variant="secondary" size="sm" type="submit">
                                Keluar
                            </x-ui.button>
                        </form>
                    @else
                        <x-ui.button variant="ghost" size="sm" :href="route('login')">
                            Masuk
                        </x-ui.button>
                        <x-ui.button variant="primary" size="sm" :href="route('register')">
                            Daftar
                        </x-ui.button>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200/70 py-10 dark:border-zinc-800">
            <div class="mx-auto max-w-[var(--container-5xl)] px-4">
                <div class="flex flex-col gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                    <div class="font-semibold text-zinc-900 dark:text-white">EPIC Hub Premium</div>
                    <div>Platform produk digital premium, kelas, event, dan peluang bertumbuh bersama.</div>
                    <div class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">
                        © {{ now()->year }} EPIC Hub Premium. Semua hak dilindungi.
                    </div>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
