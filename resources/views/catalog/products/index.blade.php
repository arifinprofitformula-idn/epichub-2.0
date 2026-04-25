<x-layouts::public title="Produk">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            eyebrow="Katalog"
            title="Produk Digital"
            description="Temukan produk digital premium yang bisa kamu akses dari satu tempat."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('home')">
                Kembali
            </x-ui.button>
        </x-ui.section-header>

        <x-ui.card class="mt-6 p-5">
            <form method="GET" action="{{ route('catalog.products.index') }}" class="grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Cari</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari judul produk..."
                        class="mt-1 w-full rounded-[var(--radius-md)] border border-zinc-200/70 bg-white px-3 py-2 text-sm text-zinc-900 shadow-[var(--shadow-soft)] outline-none focus:ring-2 focus:ring-accent dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                    />
                </div>

                <div>
                    <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Kategori</label>
                    <select
                        name="category"
                        class="mt-1 w-full rounded-[var(--radius-md)] border border-zinc-200/70 bg-white px-3 py-2 text-sm text-zinc-900 shadow-[var(--shadow-soft)] outline-none focus:ring-2 focus:ring-accent dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                    >
                        <option value="">Semua</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Tipe</label>
                    <select
                        name="type"
                        class="mt-1 w-full rounded-[var(--radius-md)] border border-zinc-200/70 bg-white px-3 py-2 text-sm text-zinc-900 shadow-[var(--shadow-soft)] outline-none focus:ring-2 focus:ring-accent dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                    >
                        <option value="">Semua</option>
                        <option value="ebook" @selected($activeType === 'ebook')>Ebook</option>
                        <option value="course" @selected($activeType === 'course')>Ecourse</option>
                        <option value="membership" @selected($activeType === 'membership')>Membership</option>
                        <option value="event" @selected($activeType === 'event')>Event</option>
                        <option value="bundle" @selected($activeType === 'bundle')>Bundle</option>
                        <option value="digital_file" @selected($activeType === 'digital_file')>Digital file</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        Menampilkan {{ $products->total() }} produk
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button variant="secondary" size="sm" type="submit">
                            Terapkan
                        </x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.index')">
                            Reset
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        @if ($products->count() === 0)
            <div class="mt-8">
                <x-ui.empty-state
                    title="Belum ada produk"
                    description="Saat ini belum ada produk yang dipublish. Coba lagi nanti."
                    action-label="Kembali ke Home"
                    :action-href="route('home')"
                />
            </div>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <a href="{{ route('catalog.products.show', $product->slug) }}" class="group">
                        <x-ui.card class="h-full overflow-hidden">
                            <div class="aspect-[16/10] bg-zinc-100 dark:bg-zinc-800">
                                @if (filled($product->thumbnail))
                                    <img
                                        src="{{ asset('storage/'.$product->thumbnail) }}"
                                        alt="{{ $product->title }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="flex h-full items-center justify-center text-xs text-zinc-500 dark:text-zinc-400">
                                        Tanpa thumbnail
                                    </div>
                                @endif
                            </div>

                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <x-ui.badge variant="info">{{ $product->product_type?->label() ?? $product->product_type }}</x-ui.badge>
                                    @if ($product->has_discount)
                                        <x-ui.badge variant="warning">Promo</x-ui.badge>
                                    @endif
                                </div>

                                <div class="mt-3 text-base font-semibold tracking-tight text-zinc-900 group-hover:underline dark:text-white">
                                    {{ $product->title }}
                                </div>

                                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $product->category?->name ?? 'Tanpa kategori' }}
                                </div>

                                <div class="mt-4 flex items-end justify-between gap-3">
                                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                        @if ($product->has_discount)
                                            <div class="text-xs line-through opacity-70">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</div>
                                        @endif
                                        <div class="text-base font-semibold text-zinc-900 dark:text-white">
                                            Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">
                                        Detail
                                    </x-ui.button>
                                </div>
                            </div>
                        </x-ui.card>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </section>
</x-layouts::public>
