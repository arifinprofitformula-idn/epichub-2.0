@component('layouts::app', ['title' => __('Marketplace')])
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <x-ui.section-header
                eyebrow="Marketplace"
                title="Marketplace"
                description="Temukan produk digital, kelas, event, dan bundle pilihan EPIC HUB."
            >
                <x-ui.button variant="ghost" size="sm" :href="route('dashboard')">
                    Kembali ke dashboard
                </x-ui.button>
            </x-ui.section-header>

            <div class="mt-6">
                <x-ui.card class="p-6">
                    <form method="GET" action="{{ route('marketplace.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_repeat(3,minmax(0,0.8fr))_auto]">
                        <div>
                            <label for="q" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Cari produk</label>
                            <input
                                id="q"
                                name="q"
                                type="text"
                                value="{{ $activeFilters['q'] }}"
                                placeholder="Cari ebook, kelas, event, bundle..."
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300"
                            />
                        </div>

                        <div>
                            <label for="category" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Kategori</label>
                            <select id="category" name="category" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->slug }}" @selected($activeFilters['category'] === $category->slug)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="product_type" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Tipe produk</label>
                            <select id="product_type" name="product_type" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300">
                                <option value="">Semua tipe</option>
                                @foreach ($productTypes as $type)
                                    <option value="{{ $type['value'] }}" @selected($activeFilters['product_type'] === $type['value'])>{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="ownership" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Ownership</label>
                            <select id="ownership" name="ownership" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300">
                                <option value="all" @selected($activeFilters['ownership'] === 'all')>Semua</option>
                                <option value="owned" @selected($activeFilters['ownership'] === 'owned')>Sudah dimiliki</option>
                                <option value="not_owned" @selected($activeFilters['ownership'] === 'not_owned')>Belum dimiliki</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <x-ui.button variant="primary" size="md" type="submit">Terapkan</x-ui.button>
                            <x-ui.button variant="ghost" size="md" :href="route('marketplace.index')">Reset</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @php
                    $chipTypes = [
                        '' => 'Semua',
                        'ebook' => 'Ebook',
                        'digital_file' => 'Digital File',
                        'course' => 'Course',
                        'event' => 'Event',
                        'bundle' => 'Bundle',
                        'membership' => 'Membership',
                    ];
                @endphp

                @foreach ($chipTypes as $chipValue => $chipLabel)
                    @php
                        $chipActive = $activeFilters['product_type'] === $chipValue || ($chipValue === '' && $activeFilters['product_type'] === '');
                        $chipParams = [
                            'q' => $activeFilters['q'],
                            'category' => $activeFilters['category'],
                            'product_type' => $chipValue !== '' ? $chipValue : null,
                            'ownership' => $activeFilters['ownership'] !== 'all' ? $activeFilters['ownership'] : null,
                        ];
                        $chipHref = route('marketplace.index', array_filter($chipParams, function ($value) {
                            return $value !== null && $value !== '';
                        }));
                    @endphp
                    <a
                        href="{{ $chipHref }}"
                        class="{{ $chipActive ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:border-cyan-300 hover:text-cyan-700' }} inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold shadow-sm transition"
                    >
                        {{ $chipLabel }}
                    </a>
                @endforeach
            </div>

            @if ($products->count() === 0)
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Belum ada produk yang sesuai"
                        description="Coba ubah keyword pencarian atau filter Marketplace untuk melihat produk lain."
                        action-label="Reset Filter"
                        :action-href="route('marketplace.index')"
                    />
                </div>
            @else
                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        @php
                            $userProduct = $ownedUserProducts->get($product->id);
                            $eventRegistration = $eventRegistrationsByProductId->get($product->id);
                            $progress = $userProduct ? ($progressByUserProductId[$userProduct->id] ?? null) : null;
                            $detailUrl = route('catalog.products.show', $product->slug);
                            $checkoutUrl = route('checkout.show', $product->slug);
                            $accessUrl = null;
                            $primaryLabel = 'Beli Sekarang';

                            if ($userProduct) {
                                $typeValue = $product->product_type?->value ?? (string) $product->product_type;

                                switch ($typeValue) {
                                    case 'course':
                                        $accessUrl = route('my-courses.show', $userProduct);
                                        $primaryLabel = 'Masuk Kelas';
                                        break;
                                    case 'event':
                                        $accessUrl = $eventRegistration
                                            ? route('my-events.show', $eventRegistration)
                                            : route('my-events.index');
                                        $primaryLabel = 'Lihat Event';
                                        break;
                                    case 'ebook':
                                        $accessUrl = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Akses Ebook';
                                        break;
                                    case 'digital_file':
                                        $accessUrl = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Akses File';
                                        break;
                                    case 'bundle':
                                        $accessUrl = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Akses Bundle';
                                        break;
                                    case 'membership':
                                        $accessUrl = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Lihat Akses';
                                        break;
                                    default:
                                        $accessUrl = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Akses';
                                        break;
                                }
                            } else {
                                $primaryLabel = match ($product->product_type?->value ?? (string) $product->product_type) {
                                    'event' => 'Beli Tiket',
                                    'bundle' => 'Ambil Bundle',
                                    'membership' => 'Beli Membership',
                                    default => 'Beli Sekarang',
                                };
                            }
                        @endphp

                        <x-marketplace.product-card
                            :product="$product"
                            :user-product="$userProduct"
                            :event-registration="$eventRegistration"
                            :progress="$progress"
                            :access-url="$accessUrl"
                            :checkout-url="$checkoutUrl"
                            :detail-url="$detailUrl"
                            :primary-label="$primaryLabel"
                        />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
