<x-layouts::public title="Checkout">
    <section class="mx-auto max-w-[var(--container-6xl)] px-4 py-8">

        {{-- Top bar --}}
        <div class="mb-6 flex items-center justify-between gap-3">
            <a
                href="{{ route('catalog.products.show', $product->slug) }}"
                class="btn-3d-nav inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700"
                style="box-shadow: 0 4px 0 0 #d4d4d8, 0 6px 12px rgba(0,0,0,0.07); transition: transform 0.1s ease, box-shadow 0.1s ease;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 0 0 #d4d4d8, 0 10px 16px rgba(0,0,0,0.1)';"
                onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 0 0 #d4d4d8, 0 6px 12px rgba(0,0,0,0.07)';"
                onmousedown="this.style.transform='translateY(3px)'; this.style.boxShadow='0 1px 0 0 #d4d4d8';"
                onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 0 0 #d4d4d8, 0 10px 16px rgba(0,0,0,0.1)';"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/>
                </svg>
                <span class="hidden sm:inline">Kembali ke Produk</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-zinc-600">
                    <svg class="h-3 w-3 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                    Checkout Aman
                </span>
                @guest
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                        Masuk
                    </a>
                @endguest
            </div>
        </div>

        {{-- Breadcrumb-style header --}}
        <div class="mb-6">
            <div class="flex flex-wrap items-center gap-1.5 text-xs text-zinc-400">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 1.75A.75.75 0 0 1 1.75 1h1.628a1.75 1.75 0 0 1 1.734 1.51L5.18 3a65.25 65.25 0 0 1 13.36 1.412.75.75 0 0 1 .58.875 48.645 48.645 0 0 1-1.618 6.2.75.75 0 0 1-.712.513H6a2.503 2.503 0 0 0-2.292 1.5H17.25a.75.75 0 0 1 0 1.5H2.76a.75.75 0 0 1-.748-.807 4.002 4.002 0 0 1 2.716-3.486L3.626 2.716a.25.25 0 0 0-.248-.216H1.75A.75.75 0 0 1 1 1.75Z" clip-rule="evenodd"/></svg>
                <span class="font-semibold text-zinc-900">Checkout</span>
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                <span class="truncate max-w-xs text-zinc-500">{{ $product->title }}</span>
            </div>
        </div>

        @include('checkout.partials.content')
    </section>
</x-layouts::public>
