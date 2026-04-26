<x-layouts::app title="Checkout">
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <x-ui.section-header
                eyebrow="Checkout"
                title="Checkout Produk"
                :description="'Selesaikan pembelian tanpa keluar dari member area dashboard.'"
            >
                <x-ui.button variant="ghost" size="sm" :href="route('marketplace.index')">
                    Kembali ke marketplace
                </x-ui.button>
            </x-ui.section-header>

            <div class="mt-6">
                @include('checkout.partials.content')
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
</x-layouts::app>
