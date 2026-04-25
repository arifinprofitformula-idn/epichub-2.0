<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap');

            .epi-public-shell {
                font-family: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif;
                background:
                    radial-gradient(850px 420px at 12% -10%, #ffffff, transparent 60%),
                    radial-gradient(900px 450px at 100% 120%, rgba(37, 99, 235, 0.08), transparent 62%),
                    #e8ebef;
            }

            .epi-public-nav {
                background: rgba(255, 255, 255, 0.9);
            }

            .epi-public-main {
                min-height: calc(100vh - 10rem);
            }

            .epi-public-main h1,
            .epi-public-main h2,
            .epi-public-main h3 {
                font-family: "Sora", "Plus Jakarta Sans", sans-serif;
                letter-spacing: -0.02em;
            }

            .epi-public-footer {
                background: rgba(255, 255, 255, 0.65);
                border-top: 1px solid #d9e0e8;
            }
        </style>
    </head>
    <body class="min-h-screen antialiased epi-public-shell">
        <header class="sticky top-0 z-50 w-full border-b border-slate-200/80 epi-public-nav backdrop-blur">
            <div class="mx-auto flex max-w-[var(--container-5xl)] items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-slate-900">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm">
                        <x-app-logo-icon class="size-6 fill-current text-amber-700" />
                    </span>
                    <span class="text-sm tracking-tight">EPIC Hub Premium</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                    <a href="{{ route('home') }}" class="hover:text-slate-900">Home</a>
                    <a href="{{ route('catalog.products.index') }}" class="hover:text-slate-900">Produk</a>
                    <a href="{{ route('home') }}#event" class="hover:text-slate-900">Event</a>
                    <a href="{{ route('home') }}#membership" class="hover:text-slate-900">Membership</a>
                    <a href="{{ route('home') }}#epi-channel" class="hover:text-slate-900">EPI Channel</a>
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

        <main class="epi-public-main pt-6">
            {{ $slot }}
        </main>

        <footer class="py-10 epi-public-footer">
            <div class="mx-auto max-w-[var(--container-5xl)] px-4">
                <div class="flex flex-col gap-2 text-sm text-slate-600">
                    <div class="font-semibold text-slate-900">EPIC Hub Premium</div>
                    <div>Platform produk digital premium, kelas, event, dan peluang bertumbuh bersama.</div>
                    <div class="mt-4 text-xs text-slate-500">
                        © {{ now()->year }} EPIC Hub Premium. Semua hak dilindungi.
                    </div>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
