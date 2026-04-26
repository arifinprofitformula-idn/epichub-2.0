<x-layouts::app :title="$product->title">
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white/92 p-6 shadow-[0_20px_45px_rgba(15,23,42,0.08)] md:p-8">
                <div class="mb-6">
                    <x-ui.button variant="ghost" size="sm" :href="route('marketplace.index')">
                        ← Kembali ke marketplace
                    </x-ui.button>
                </div>

                @include('catalog.products.partials.show-content')
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
</x-layouts::app>
