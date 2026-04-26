<x-layouts::app :title="__('Link Promosi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 shadow-sm">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M13.5 10.5L17.5 6.5M17.5 6.5H14.5M17.5 6.5V9.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.5 13.5L6.5 17.5M6.5 17.5H9.5M6.5 17.5V14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5 17.5L17.5 14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            <path d="M6.5 9.5L9.5 6.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400">EPI Channel</span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Link Promosi</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Bagikan referral link, landing page, dan checkout link untuk setiap produk affiliate aktif kamu.</p>
            </div>
            <a href="{{ route('epi-channel.dashboard') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 shadow-sm transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow active:scale-[0.98] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M9.25 19.25L4.75 12L9.25 4.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4.75 12H19.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Referral Link Utama - Hero Card --}}
        <div class="mt-6">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-blue-600 p-[1px] shadow-lg shadow-violet-200 dark:shadow-violet-900/30">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-blue-600 p-6 sm:p-7">
                    {{-- Decorative circles --}}
                    <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/5"></div>
                    <div class="pointer-events-none absolute -bottom-12 -left-4 size-32 rounded-full bg-white/5"></div>
                    <div class="pointer-events-none absolute right-16 bottom-4 size-16 rounded-full bg-white/5"></div>

                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                            <svg viewBox="0 0 24 24" fill="none" class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M10 13.5C10.918 14.7144 12.2986 15.4762 13.8001 15.5936C15.3017 15.7111 16.7808 15.1736 17.875 14.125L20.375 11.625C22.3747 9.55533 22.3163 6.27268 20.2466 4.27344C18.177 2.27419 14.8943 2.33253 12.8946 4.40214L11.4595 5.85072" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 10.5C13.082 9.28559 11.7014 8.52384 10.1999 8.40637C8.69836 8.28889 7.21922 8.82641 6.125 9.875L3.625 12.375C1.62534 14.4447 1.68368 17.7273 3.75329 19.7266C5.82289 21.7258 9.10554 21.6675 11.1054 19.5979L12.5317 18.1406" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-200">Referral Link Utama</div>
                            <div class="mt-1 text-base font-semibold text-white">Link global EPI Channel kamu</div>
                            <div class="mt-3">
                                @include('epi-channel.partials.copy-field', [
                                    'label' => '',
                                    'value' => $mainReferralLink,
                                    'fieldId' => 'epi-channel-main-referral-link',
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $colorSets = [
                ['from' => 'from-blue-500', 'to' => 'to-cyan-500', 'light' => 'bg-blue-50', 'lightDark' => 'dark:bg-blue-900/20', 'text' => 'text-blue-600', 'textDark' => 'dark:text-blue-400', 'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300', 'shadow' => 'shadow-blue-100 dark:shadow-blue-900/20', 'border' => 'border-blue-100 dark:border-blue-800/40'],
                ['from' => 'from-violet-500', 'to' => 'to-purple-600', 'light' => 'bg-violet-50', 'lightDark' => 'dark:bg-violet-900/20', 'text' => 'text-violet-600', 'textDark' => 'dark:text-violet-400', 'badge' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300', 'shadow' => 'shadow-violet-100 dark:shadow-violet-900/20', 'border' => 'border-violet-100 dark:border-violet-800/40'],
                ['from' => 'from-emerald-500', 'to' => 'to-teal-500', 'light' => 'bg-emerald-50', 'lightDark' => 'dark:bg-emerald-900/20', 'text' => 'text-emerald-600', 'textDark' => 'dark:text-emerald-400', 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'shadow' => 'shadow-emerald-100 dark:shadow-emerald-900/20', 'border' => 'border-emerald-100 dark:border-emerald-800/40'],
                ['from' => 'from-orange-500', 'to' => 'to-amber-500', 'light' => 'bg-orange-50', 'lightDark' => 'dark:bg-orange-900/20', 'text' => 'text-orange-600', 'textDark' => 'dark:text-orange-400', 'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300', 'shadow' => 'shadow-orange-100 dark:shadow-orange-900/20', 'border' => 'border-orange-100 dark:border-orange-800/40'],
                ['from' => 'from-rose-500', 'to' => 'to-pink-500', 'light' => 'bg-rose-50', 'lightDark' => 'dark:bg-rose-900/20', 'text' => 'text-rose-600', 'textDark' => 'dark:text-rose-400', 'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300', 'shadow' => 'shadow-rose-100 dark:shadow-rose-900/20', 'border' => 'border-rose-100 dark:border-rose-800/40'],
            ];
        @endphp

        {{-- Product Cards --}}
        @if ($products->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada produk affiliate"
                    description="Admin belum mengaktifkan affiliate untuk produk mana pun."
                />
            </div>
        @else
            <div class="mt-6 grid gap-5 xl:grid-cols-2">
                @foreach ($products as $product)
                    @php($productLink = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code)
                    @php($landingLink = $product->landing_page_enabled ? route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code]) : null)
                    @php($checkoutLink = route('checkout.show', $product->slug).'?ref='.$channel->epic_code)
                    @php($productPalette = $colorSets[$loop->index % count($colorSets)])

                    <div class="group relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 {{ $productPalette['shadow'] }}">
                        {{-- Top accent bar --}}
                        <div class="h-1 w-full bg-gradient-to-r {{ $productPalette['from'] }} {{ $productPalette['to'] }}"></div>

                        <div class="p-5 sm:p-6">
                            {{-- Product header --}}
                            <div class="flex items-start gap-4">
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $productPalette['from'] }} {{ $productPalette['to'] }} shadow-sm">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M4.75 5.75A1 1 0 0 1 5.75 4.75h12.5a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1 1H5.75a1 1 0 0 1-1-1V5.75Z" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".15"/>
                                        <path d="M8.5 10.5h7M8.5 13.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-base font-bold text-zinc-900 dark:text-white leading-snug">{{ $product->title }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $productPalette['badge'] }}">
                                            {{ $product->product_type?->label() ?? $product->product_type?->value ?? 'Produk' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="text-base font-bold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                    </div>
                                    @if ($product->affiliate_commission_type)
                                        <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ $product->affiliate_commission_type->label() }} {{ (float) $product->affiliate_commission_value }}
                                        </div>
                                    @else
                                        <div class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Komisi belum diatur</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Link fields --}}
                            <div class="mt-5 space-y-4">
                                {{-- Product referral link --}}
                                <div class="rounded-xl border {{ $productPalette['border'] }} {{ $productPalette['light'] }} {{ $productPalette['lightDark'] }} p-3.5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex size-5 shrink-0 items-center justify-center rounded-md bg-gradient-to-br {{ $productPalette['from'] }} {{ $productPalette['to'] }}">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M10 13.5C10.918 14.7144 12.2986 15.4762 13.8001 15.5936C15.3017 15.7111 16.7808 15.1736 17.875 14.125L20.375 11.625C22.3747 9.55533 22.3163 6.27268 20.2466 4.27344C18.177 2.27419 14.8943 2.33253 12.8946 4.40214L11.4595 5.85072" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14 10.5C13.082 9.28559 11.7014 8.52384 10.1999 8.40637C8.69836 8.28889 7.21922 8.82641 6.125 9.875L3.625 12.375C1.62534 14.4447 1.68368 17.7273 3.75329 19.7266C5.82289 21.7258 9.10554 21.6675 11.1054 19.5979L12.5317 18.1406" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-widest {{ $productPalette['text'] }} {{ $productPalette['textDark'] }}">Link Produk</span>
                                    </div>
                                    @include('epi-channel.partials.copy-field', [
                                        'label' => '',
                                        'value' => $productLink,
                                        'fieldId' => 'product-ref-link-'.$product->id,
                                    ])
                                </div>

                                @if ($landingLink)
                                    {{-- Landing page link --}}
                                    <div class="rounded-xl border border-amber-100 bg-amber-50 p-3.5 dark:border-amber-800/40 dark:bg-amber-900/20">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="flex size-5 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-amber-500 to-orange-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M3.75 6.75C3.75 5.64543 4.64543 4.75 5.75 4.75H18.25C19.3546 4.75 20.25 5.64543 20.25 6.75V14.25C20.25 15.3546 19.3546 16.25 18.25 16.25H5.75C4.64543 16.25 3.75 15.3546 3.75 14.25V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8.75 19.25H15.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M12 16.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                            <span class="text-xs font-bold uppercase tracking-widest text-amber-600 dark:text-amber-400">Landing Page</span>
                                        </div>
                                        @include('epi-channel.partials.copy-field', [
                                            'label' => '',
                                            'value' => $landingLink,
                                            'fieldId' => 'product-landing-link-'.$product->id,
                                        ])
                                    </div>
                                @endif

                                {{-- Checkout link --}}
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3.5 dark:border-emerald-800/40 dark:bg-emerald-900/20">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex size-5 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-emerald-500 to-teal-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="9" cy="20.25" r="1.25" fill="currentColor"/>
                                                <circle cx="15.5" cy="20.25" r="1.25" fill="currentColor"/>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Checkout</span>
                                    </div>
                                    @include('epi-channel.partials.copy-field', [
                                        'label' => '',
                                        'value' => $checkoutLink,
                                        'fieldId' => 'product-checkout-link-'.$product->id,
                                    ])
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div class="mt-5 flex flex-wrap gap-2">
                                <a href="{{ route('catalog.products.show', $product->slug) }}"
                                   target="_blank"
                                   class="group inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-600 shadow-sm transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow active:scale-[0.97] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M9.75 12H14.25M14.25 12L12 9.75M14.25 12L12 14.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Detail Produk
                                </a>

                                @if ($landingLink)
                                    <a href="{{ $landingLink }}" target="_blank"
                                       class="group inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2 text-xs font-semibold text-amber-700 shadow-sm transition-all duration-200 hover:border-amber-300 hover:bg-amber-100 hover:shadow active:scale-[0.97] dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/40">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M3.75 6.75C3.75 5.64543 4.64543 4.75 5.75 4.75H18.25C19.3546 4.75 20.25 5.64543 20.25 6.75V14.25C20.25 15.3546 19.3546 16.25 18.25 16.25H5.75C4.64543 16.25 3.75 15.3546 3.75 14.25V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8.75 19.25H15.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M12 16.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        Landing Page
                                    </a>
                                @endif

                                <a href="{{ $checkoutLink }}" target="_blank"
                                   class="group inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-100 hover:shadow active:scale-[0.97] dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                        <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="9" cy="20.25" r="1.25" fill="currentColor"/>
                                        <circle cx="15.5" cy="20.25" r="1.25" fill="currentColor"/>
                                    </svg>
                                    Beli Sekarang
                                </a>

                                {{-- Share button --}}
                                <button
                                    type="button"
                                    onclick="epicShareLink(this, '{{ $productLink }}', '{{ addslashes($product->title) }}')"
                                    class="group inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-semibold text-blue-700 shadow-sm transition-all duration-200 hover:border-blue-300 hover:bg-blue-100 hover:shadow active:scale-[0.97] dark:border-blue-700/50 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <circle cx="18" cy="5" r="2.25" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="6" cy="12" r="2.25" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="18" cy="19" r="2.25" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8.25 10.75L15.75 6.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M8.25 13.25L15.75 17.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Bagikan
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif

    @include('epi-channel.partials.page-shell-end')

    @once
    <script>
    function epicShareLink(btn, url, title) {
        if (navigator.share) {
            navigator.share({ title: title, url: url }).catch(function() {});
        } else {
            navigator.clipboard.writeText(url).then(function() {
                var orig = btn.innerHTML;
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Link Disalin!';
                btn.classList.add('border-emerald-300', 'bg-emerald-50', 'text-emerald-700');
                btn.classList.remove('border-blue-200', 'bg-blue-50', 'text-blue-700');
                setTimeout(function() {
                    btn.innerHTML = orig;
                    btn.classList.remove('border-emerald-300', 'bg-emerald-50', 'text-emerald-700');
                    btn.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-700');
                }, 2000);
            });
        }
    }
    </script>
    @endonce
</x-layouts::app>
