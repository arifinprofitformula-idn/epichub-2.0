<div class="grid gap-8 lg:grid-cols-5">
    <div class="lg:col-span-3">
        <x-ui.card class="overflow-hidden">
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

            <div class="p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge variant="info">{{ $product->product_type?->label() ?? $product->product_type }}</x-ui.badge>
                    @if (! empty($ownedUserProduct))
                        <x-ui.badge variant="success">Sudah dimiliki</x-ui.badge>
                    @endif
                    @if ($product->is_featured)
                        <x-ui.badge variant="warning">Unggulan</x-ui.badge>
                    @endif
                    @if ($product->category)
                        <x-ui.badge variant="neutral">{{ $product->category->name }}</x-ui.badge>
                    @endif
                </div>

                <h1 class="mt-4 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white md:text-3xl">
                    {{ $product->title }}
                </h1>

                @if (filled($product->short_description))
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
                        {{ $product->short_description }}
                    </p>
                @endif

                @if (filled($product->full_description))
                    <div class="prose prose-zinc mt-6 max-w-none dark:prose-invert">
                        {!! nl2br(e(strip_tags($product->full_description))) !!}
                    </div>
                @endif
            </div>
        </x-ui.card>

        @if ($product->files->count() > 0)
            <div class="mt-6">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">File (metadata)</div>
                    <div class="mt-3 grid gap-3">
                        @foreach ($product->files->where('is_active', true)->sortBy('sort_order') as $file)
                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $file->title }}</div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $file->file_type ?: 'file' }}
                                </div>
                                <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-300">
                                    File delivery akan tersedia setelah checkout aktif.
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>
        @endif
    </div>

    <div class="lg:col-span-2">
        <x-ui.card class="p-6 md:p-8">
            @if (! empty($ownedUserProduct))
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Akses Produk</div>
                <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                    Produk ini sudah ada di akun Anda dan bisa dibuka langsung tanpa keluar dari dashboard.
                </div>

                @if (! empty($progress))
                    <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between gap-4 text-xs text-zinc-600 dark:text-zinc-300">
                            <span>Progress belajar</span>
                            <span class="font-semibold text-zinc-900 dark:text-white">{{ $progress['percent'] }}%</span>
                        </div>
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-zinc-200/70 dark:bg-zinc-800">
                            <div class="h-2 rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $progress['percent'] }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $progress['completed'] }} dari {{ $progress['total'] }} lesson selesai
                        </div>
                    </div>
                @endif

                <div class="mt-6 grid gap-3">
                    @if (! empty($accessUrl) && ! empty($primaryLabel))
                        <x-ui.button variant="primary" size="md" :href="$accessUrl">
                            {{ $primaryLabel }}
                        </x-ui.button>
                    @endif

                    <x-ui.button variant="secondary" size="md" :href="route('my-products.index')">
                        Lihat Produk Saya
                    </x-ui.button>

                    <x-ui.button variant="ghost" size="md" :href="route('my-products.show', $ownedUserProduct)">
                        Detail Akses
                    </x-ui.button>
                </div>
            @else
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Harga</div>
                <div class="mt-3">
                    @if ($product->has_discount)
                        <div class="text-sm text-zinc-600 dark:text-zinc-300">
                            <span class="line-through opacity-70">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                    </div>
                </div>

                <div class="mt-6 grid gap-3">
                    @if ((float) $product->effective_price > 0)
                        <x-ui.button variant="primary" size="md" :href="route('checkout.show', $product->slug)">
                            Beli Sekarang
                        </x-ui.button>
                    @else
                        <x-ui.button variant="secondary" size="md" type="button" disabled>
                            Produk belum tersedia
                        </x-ui.button>
                    @endif

                    @if ($product->landing_page_enabled)
                        <x-ui.button variant="ghost" size="md" :href="route('offer.show', $product->slug)">
                            Lihat Landing Page
                        </x-ui.button>
                    @endif

                    @if (
                        $product->landing_page_enabled
                        && $product->is_affiliate_enabled
                        && $viewerChannel?->isActive()
                    )
                        <x-ui.button variant="secondary" size="md" :href="route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $viewerChannel->epic_code])">
                            Link Landing Page Affiliate
                        </x-ui.button>
                    @endif
                </div>

                @if ((float) $product->effective_price <= 0)
                    <div class="mt-6">
                        <x-ui.alert variant="warning" title="Belum tersedia">
                            Produk ini belum tersedia untuk checkout saat ini.
                        </x-ui.alert>
                    </div>
                @endif
            @endif
        </x-ui.card>

        @if (($product->product_type?->value ?? $product->product_type) === 'bundle')
            <div class="mt-6">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Isi bundle</div>
                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                        @if ($product->bundledProducts->count() === 0)
                            Belum ada produk yang ditambahkan ke bundle ini.
                        @else
                            <ul class="list-disc pl-5">
                                @foreach ($product->bundledProducts as $bundled)
                                    <li>
                                        <a class="underline-offset-4 hover:underline" href="{{ route('catalog.products.show', $bundled->slug) }}">
                                            {{ $bundled->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        @endif
    </div>
</div>
