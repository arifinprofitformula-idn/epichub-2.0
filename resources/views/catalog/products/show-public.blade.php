<x-layouts::public :title="$product->title">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="rounded-[2rem] border border-slate-200 bg-white/92 p-6 shadow-[0_20px_45px_rgba(15,23,42,0.08)] md:p-8">
            <div class="mb-6">
                <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.index')">
                    ← Kembali ke katalog
                </x-ui.button>
            </div>

            @include('catalog.products.partials.show-content')
        </div>
    </section>
</x-layouts::public>
