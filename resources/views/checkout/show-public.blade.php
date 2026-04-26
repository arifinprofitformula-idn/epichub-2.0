<x-layouts::public title="Checkout">
    <section class="mx-auto max-w-[var(--container-6xl)] px-4 py-10">
        <div class="mb-6 flex items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">
                ← Kembali ke produk
            </x-ui.button>

            @guest
                <x-ui.button variant="ghost" size="sm" :href="route('login')">
                    Masuk
                </x-ui.button>
            @endguest
        </div>

        @include('checkout.partials.content')
    </section>
</x-layouts::public>
