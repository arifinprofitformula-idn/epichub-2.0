@component('layouts::app', ['title' => 'Produk Saya'])
    @php
        $chipTypes = [
            '' => 'Semua',
            'ebook' => 'Ebook',
            'digital_file' => 'Digital File',
            'course' => 'Kelas',
            'event' => 'Event',
            'bundle' => 'Bundle',
            'membership' => 'Membership',
        ];
    @endphp

    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <x-ui.section-header
                eyebrow="Hub Akses"
                title="Produk Saya"
                description="Semua produk, kelas, event, dan akses digital Anda berada di satu tempat."
            >
                <x-ui.button variant="ghost" size="sm" :href="route('marketplace.index')">
                    Marketplace
                </x-ui.button>
            </x-ui.section-header>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-ui.card class="p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Total Produk</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $summary['total_products'] }}</div>
                    <p class="mt-2 text-sm text-slate-500">Semua akses aktif yang sudah dimiliki di akun Anda.</p>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Produk Digital</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $summary['digital_products'] }}</div>
                    <p class="mt-2 text-sm text-slate-500">Ebook, file digital, bundle, dan membership aktif.</p>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Kelas Aktif</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $summary['active_courses'] }}</div>
                    <p class="mt-2 text-sm text-slate-500">Kelas yang siap diakses atau dilanjutkan dari dashboard Anda.</p>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Event Terdaftar</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $summary['registered_events'] }}</div>
                    <p class="mt-2 text-sm text-slate-500">Jumlah event aktif yang terhubung dengan akun Anda.</p>
                </x-ui.card>
            </div>

            <div class="mt-6">
                <x-ui.card class="p-6">
                    <form method="GET" action="{{ route('my-products.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_repeat(2,minmax(0,0.9fr))_auto]">
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
                            <label for="product_type" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Tipe produk</label>
                            <select id="product_type" name="product_type" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300">
                                <option value="">Semua tipe</option>
                                @foreach ($productTypeOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected($activeFilters['product_type'] === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Status akses</label>
                            <select id="status" name="status" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300">
                                @foreach ($statusOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected($activeFilters['status'] === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <x-ui.button variant="primary" size="md" type="submit">Terapkan</x-ui.button>
                            <x-ui.button variant="ghost" size="md" :href="route('my-products.index')">Reset</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($chipTypes as $chipValue => $chipLabel)
                    @php
                        $chipActive = $activeFilters['product_type'] === $chipValue || ($chipValue === '' && $activeFilters['product_type'] === '');
                        $chipParams = array_filter([
                            'q' => $activeFilters['q'] !== '' ? $activeFilters['q'] : null,
                            'product_type' => $chipValue !== '' ? $chipValue : null,
                            'status' => $activeFilters['status'] !== 'active' ? $activeFilters['status'] : null,
                        ], fn ($value) => $value !== null && $value !== '');
                    @endphp

                    <a
                        href="{{ route('my-products.index', $chipParams) }}"
                        class="{{ $chipActive ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:border-cyan-300 hover:text-cyan-700' }} inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold shadow-sm transition"
                    >
                        {{ $chipLabel }}
                        @if ($chipValue === '')
                            <span class="ml-2 rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ $summary['total_products'] }}</span>
                        @elseif (($groupedOwnedProducts[$chipValue] ?? collect())->count() > 0)
                            <span class="ml-2 rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ ($groupedOwnedProducts[$chipValue] ?? collect())->count() }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @if ($userProducts->count() === 0)
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Belum ada produk yang dimiliki"
                        description="Produk yang Anda beli akan muncul di sini setelah pembayaran berhasil."
                    >
                        <x-slot:action>
                            <x-ui.button variant="primary" :href="route('marketplace.index')">
                                Jelajahi Marketplace
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($userProducts as $userProduct)
                        @php
                            $product = $userProduct->product;
                            $course = $product?->course;
                            $event = $product?->event;
                            $eventRegistration = $eventRegistrationsByUserProductId->get($userProduct->id);
                            $progress = $courseProgressByUserProductId[$userProduct->id] ?? ['total' => 0, 'completed' => 0, 'percent' => 0];
                            $typeValue = $product?->product_type?->value ?? (string) $product?->product_type;
                            $typeLabel = $product?->product_type?->label() ?? ucfirst(str_replace('_', ' ', $typeValue ?: 'produk'));
                            $thumbnail = $event?->banner ?: $course?->thumbnail ?: $product?->thumbnail;
                            $thumbnailUrl = filled($thumbnail)
                                ? (\Illuminate\Support\Str::startsWith($thumbnail, ['http://', 'https://']) ? $thumbnail : asset('storage/'.$thumbnail))
                                : null;
                            $productTitle = $product?->title ?? 'Produk';
                            $productDescription = $product?->short_description
                                ?? $course?->short_description
                                ?? ($event?->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 120) : null)
                                ?? 'Akses produk Anda siap digunakan dari dashboard ini.';
                            $accessStatusLabel = $userProduct->isRevoked() ? 'Dicabut' : ($userProduct->isExpired() ? 'Kedaluwarsa' : 'Aktif');
                            $accessStatusVariant = $userProduct->isRevoked() ? 'danger' : ($userProduct->isExpired() ? 'warning' : 'success');
                            $primaryUrl = route('my-products.show', $userProduct);
                            $primaryLabel = 'Lihat Akses';

                            switch ($typeValue) {
                                case 'ebook':
                                    $primaryLabel = 'Akses Ebook';
                                    $primaryUrl = route('my-products.show', $userProduct);
                                    break;
                                case 'digital_file':
                                    $primaryLabel = 'Akses File';
                                    $primaryUrl = route('my-products.show', $userProduct);
                                    break;
                                case 'bundle':
                                    $primaryLabel = 'Akses Bundle';
                                    $primaryUrl = route('my-products.show', $userProduct);
                                    break;
                                case 'membership':
                                    $primaryLabel = 'Lihat Akses';
                                    $primaryUrl = route('my-products.show', $userProduct);
                                    break;
                                case 'course':
                                    $primaryLabel = 'Masuk Kelas';
                                    $primaryUrl = $course && $course->isPublished()
                                        ? route('my-courses.show', $userProduct)
                                        : route('my-products.show', $userProduct);
                                    break;
                                case 'event':
                                    $primaryLabel = 'Lihat Event';
                                    $primaryUrl = $eventRegistration
                                        ? route('my-events.show', $eventRegistration)
                                        : route('my-events.index');
                                    break;
                            }
                        @endphp

                        <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_20px_40px_rgba(15,23,42,0.06)]">
                            <div class="relative aspect-[16/10] bg-slate-100">
                                @if ($thumbnailUrl)
                                    <img
                                        src="{{ $thumbnailUrl }}"
                                        alt="{{ $productTitle }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.22),_transparent_42%),linear-gradient(135deg,#0f172a,#1d4ed8_45%,#f59e0b)]"></div>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent px-6 pb-5 pt-16 text-white">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge variant="info">{{ $typeLabel }}</x-ui.badge>
                                        <x-ui.badge variant="{{ $accessStatusVariant }}">{{ $accessStatusLabel }}</x-ui.badge>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <h2 class="text-xl font-semibold tracking-tight text-slate-900">{{ $productTitle }}</h2>
                                <p class="mt-2 min-h-[3.5rem] text-sm leading-relaxed text-slate-500">
                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $productDescription), 140) }}
                                </p>

                                <div class="mt-5 space-y-3 rounded-[1.25rem] border border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-600">
                                    @if (in_array($typeValue, ['ebook', 'digital_file'], true))
                                        <div class="flex items-center justify-between gap-4">
                                            <span>File tersedia</span>
                                            <span class="font-semibold text-slate-900">{{ $product?->files?->count() ?? 0 }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Diberikan</span>
                                            <span class="font-semibold text-slate-900">{{ $userProduct->granted_at?->translatedFormat('d M Y') ?? '-' }}</span>
                                        </div>
                                    @elseif ($typeValue === 'bundle')
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Isi bundle</span>
                                            <span class="font-semibold text-slate-900">{{ $product?->bundled_products_count ?? 0 }} produk</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Sumber akses</span>
                                            <span class="font-semibold text-slate-900">{{ $userProduct->source_product_id ? 'Bundle turunan' : 'Bundle utama' }}</span>
                                        </div>
                                    @elseif ($typeValue === 'membership')
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Tipe akses</span>
                                            <span class="font-semibold capitalize text-slate-900">{{ str_replace('_', ' ', (string) $userProduct->access_type) ?: 'Member' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Masa berlaku</span>
                                            <span class="font-semibold text-slate-900">{{ $userProduct->expires_at?->translatedFormat('d M Y') ?? 'Selamanya' }}</span>
                                        </div>
                                    @elseif ($typeValue === 'course')
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Progress belajar</span>
                                            <span class="font-semibold text-slate-900">{{ $progress['percent'] }}%</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Lesson selesai</span>
                                            <span class="font-semibold text-slate-900">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                                        </div>
                                    @elseif ($typeValue === 'event')
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Jadwal</span>
                                            <span class="font-semibold text-right text-slate-900">
                                                {{ $event?->starts_at?->translatedFormat('d M Y, H:i') ?? 'Menyusul' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Status registrasi</span>
                                            <span class="font-semibold text-slate-900">{{ $eventRegistration?->status?->label() ?? 'Belum terhubung' }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Kategori</span>
                                            <span class="font-semibold text-slate-900">{{ $product?->category?->name ?? 'Produk digital' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span>Masa berlaku</span>
                                            <span class="font-semibold text-slate-900">{{ $userProduct->expires_at?->translatedFormat('d M Y') ?? 'Selamanya' }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-5 flex flex-col gap-3">
                                    <a
                                        href="{{ $primaryUrl }}"
                                        class="inline-flex items-center justify-center rounded-[1rem] bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-5 py-3 text-sm font-semibold text-white shadow-[0_15px_30px_rgba(37,99,235,0.22)] transition hover:brightness-105"
                                    >
                                        {{ $primaryLabel }}
                                    </a>

                                    @if (filled($product?->slug))
                                        <a
                                            href="{{ route('catalog.products.show', $product->slug) }}"
                                            class="inline-flex items-center justify-center rounded-[1rem] border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:border-cyan-300 hover:text-cyan-700"
                                        >
                                            Detail Produk
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $userProducts->links() }}
                </div>
            @endif
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
