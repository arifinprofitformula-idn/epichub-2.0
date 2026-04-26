@component('layouts::app', ['title' => 'Akses: '.($userProduct->product?->title ?? 'Produk')])
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-ui.button variant="ghost" size="sm" :href="route('my-products.index')">
                    ← Kembali
                </x-ui.button>

                <x-ui.badge variant="{{ $userProduct->status->value === 'active' ? 'success' : 'neutral' }}">
                    {{ $userProduct->status->label() }}
                </x-ui.badge>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <x-ui.card class="p-6 md:p-8">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Detail akses</div>

                        <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $userProduct->product?->title ?? 'Produk' }}
                            </div>
                            <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $userProduct->product?->product_type?->label() ?? ($userProduct->product?->product_type ?? '-') }}
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 text-sm">
                            <div class="flex items-center justify-between gap-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                <div class="text-zinc-600 dark:text-zinc-300">Diberikan pada</div>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $userProduct->granted_at?->format('d M Y, H:i') ?? '-' }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                <div class="text-zinc-600 dark:text-zinc-300">Sumber</div>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    @if ($userProduct->source_product_id)
                                        Bundle
                                    @elseif ($userProduct->order_id)
                                        Order
                                    @else
                                        Manual
                                    @endif
                                </div>
                            </div>

                            @if ($userProduct->order)
                                <div class="flex items-center justify-between gap-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                    <div class="text-zinc-600 dark:text-zinc-300">Order No.</div>
                                    <div class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $userProduct->order->order_number }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6">
                            @php($type = $delivery['type'] ?? null)

                            @if ($type === 'ebook' || $type === 'digital_file')
                                @if (($delivery['files'] ?? collect())->count() === 0)
                                    <x-ui.empty-state
                                        title="Belum ada file"
                                        description="Konten untuk produk ini belum ditambahkan. Silakan cek lagi nanti."
                                    />
                                @else
                                    <div class="grid gap-3">
                                        @foreach (($delivery['files'] ?? collect()) as $file)
                                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                        {{ $file->title }}
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                                                        @if (filled($file->file_type))
                                                            <x-ui.badge variant="neutral">{{ $file->file_type }}</x-ui.badge>
                                                        @endif
                                                        @if (filled($file->file_path))
                                                            <x-ui.badge variant="info">File</x-ui.badge>
                                                        @endif
                                                        @if (filled($file->external_url))
                                                            <x-ui.badge variant="info">Link</x-ui.badge>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @php($path = (string) ($file->file_path ?? ''))
                                                    @php($pathLower = strtolower($path))
                                                    @php($isProbablyViewable = str_ends_with($pathLower, '.pdf') || str_ends_with($pathLower, '.png') || str_ends_with($pathLower, '.jpg') || str_ends_with($pathLower, '.jpeg') || str_ends_with($pathLower, '.webp') || str_ends_with($pathLower, '.gif'))

                                                    @if (filled($file->file_path) && $isProbablyViewable)
                                                        <x-ui.button
                                                            variant="secondary"
                                                            size="sm"
                                                            :href="route('my-products.files.view', [$userProduct, $file])"
                                                        >
                                                            Lihat
                                                        </x-ui.button>
                                                    @endif

                                                    @if (filled($file->file_path))
                                                        <x-ui.button
                                                            variant="secondary"
                                                            size="sm"
                                                            :href="route('my-products.files.download', [$userProduct, $file])"
                                                        >
                                                            Download
                                                        </x-ui.button>
                                                    @endif

                                                    @if (filled($file->external_url))
                                                        <x-ui.button
                                                            variant="secondary"
                                                            size="sm"
                                                            :href="route('my-products.files.open', [$userProduct, $file])"
                                                        >
                                                            Buka link
                                                        </x-ui.button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif ($type === 'bundle')
                                @if (($delivery['childUserProducts'] ?? collect())->count() === 0)
                                    <x-ui.empty-state
                                        title="Belum ada produk bundle"
                                        description="Produk dalam bundle ini belum diberikan ke akun Anda."
                                    />
                                @else
                                    <div class="grid gap-3">
                                        @foreach (($delivery['childUserProducts'] ?? collect()) as $childUserProduct)
                                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                                <div class="flex flex-wrap items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                            {{ $childUserProduct->product?->title ?? 'Produk' }}
                                                        </div>
                                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                                                            <x-ui.badge variant="info">
                                                                {{ $childUserProduct->product?->product_type?->label() ?? ($childUserProduct->product?->product_type ?? '-') }}
                                                            </x-ui.badge>
                                                            <x-ui.badge variant="{{ $childUserProduct->status->value === 'active' ? 'success' : 'neutral' }}">
                                                                {{ $childUserProduct->status->label() }}
                                                            </x-ui.badge>
                                                        </div>
                                                    </div>
                                                    <x-ui.button variant="secondary" size="sm" :href="route('my-products.show', $childUserProduct)">
                                                        Lihat akses
                                                    </x-ui.button>
                                                </div>

                                                @php($childFiles = ($childUserProduct->product?->files ?? collect())->take(3))

                                                @if ($childFiles->count() > 0)
                                                    <div class="mt-4 grid gap-2">
                                                        @foreach ($childFiles as $file)
                                                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-xl)] bg-zinc-50 p-3 text-sm dark:bg-zinc-900/40">
                                                                <div class="min-w-0">
                                                                    <div class="truncate font-semibold text-zinc-900 dark:text-white">
                                                                        {{ $file->title }}
                                                                    </div>
                                                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                                                                        @if (filled($file->file_type))
                                                                            <x-ui.badge variant="neutral">{{ $file->file_type }}</x-ui.badge>
                                                                        @endif
                                                                        @if (filled($file->file_path))
                                                                            <x-ui.badge variant="info">File</x-ui.badge>
                                                                        @endif
                                                                        @if (filled($file->external_url))
                                                                            <x-ui.badge variant="info">Link</x-ui.badge>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    @php($path = (string) ($file->file_path ?? ''))
                                                                    @php($pathLower = strtolower($path))
                                                                    @php($isProbablyViewable = str_ends_with($pathLower, '.pdf') || str_ends_with($pathLower, '.png') || str_ends_with($pathLower, '.jpg') || str_ends_with($pathLower, '.jpeg') || str_ends_with($pathLower, '.webp') || str_ends_with($pathLower, '.gif'))

                                                                    @if (filled($file->file_path) && $isProbablyViewable)
                                                                        <x-ui.button
                                                                            variant="secondary"
                                                                            size="sm"
                                                                            :href="route('my-products.files.view', [$childUserProduct, $file])"
                                                                        >
                                                                            Lihat
                                                                        </x-ui.button>
                                                                    @endif

                                                                    @if (filled($file->file_path))
                                                                        <x-ui.button
                                                                            variant="secondary"
                                                                            size="sm"
                                                                            :href="route('my-products.files.download', [$childUserProduct, $file])"
                                                                        >
                                                                            Download
                                                                        </x-ui.button>
                                                                    @endif

                                                                    @if (filled($file->external_url))
                                                                        <x-ui.button
                                                                            variant="secondary"
                                                                            size="sm"
                                                                            :href="route('my-products.files.open', [$childUserProduct, $file])"
                                                                        >
                                                                            Buka link
                                                                        </x-ui.button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif ($type === 'course')
                                @php($course = $userProduct->product?->course)
                                @php($courseReady = (bool) $course)

                                @if ($courseReady)
                                    <x-ui.alert variant="info" title="Course tersedia">
                                        Materi kelas sudah tersedia. Klik tombol di bawah untuk mulai belajar.
                                    </x-ui.alert>

                                    <div class="mt-4">
                                        <x-ui.button variant="primary" :href="route('my-courses.show', $userProduct)">
                                            Masuk Kelas
                                        </x-ui.button>
                                    </div>
                                @else
                                    <x-ui.alert variant="info" title="Catatan">
                                        Materi kelas sedang disiapkan.
                                    </x-ui.alert>
                                @endif
                            @elseif ($type === 'event')
                                @php($registration = $delivery['eventRegistration'] ?? null)

                                <x-ui.alert variant="info" title="Event tersedia">
                                    Event ini bisa diakses dari halaman Event Saya.
                                </x-ui.alert>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    @if ($registration)
                                        <x-ui.button variant="primary" :href="route('my-events.show', $registration)">
                                            Lihat Event
                                        </x-ui.button>
                                    @else
                                        <x-ui.button variant="primary" :href="route('my-events.index')">
                                            Event Saya
                                        </x-ui.button>
                                    @endif
                                </div>
                            @elseif (filled($delivery['placeholderMessage'] ?? null))
                                <x-ui.alert variant="info" :title="$delivery['placeholderTitle'] ?? 'Catatan'">
                                    {{ $delivery['placeholderMessage'] }}
                                </x-ui.alert>
                            @else
                                <x-ui.alert variant="info" title="Catatan">
                                    Delivery konten akan dibuka bertahap sesuai modul yang aktif.
                                </x-ui.alert>
                            @endif
                        </div>
                    </x-ui.card>
                </div>

                <div class="lg:col-span-2">
                    <x-ui.card class="p-6 md:p-8">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Aksi</div>

                        <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                            Gunakan tombol di daftar file untuk mengunduh, melihat, atau membuka link. Semua akses dicek berdasarkan entitlement aktif.
                        </div>

                        <div class="mt-6">
                            <x-ui.button variant="secondary" :href="route('catalog.products.index')">
                                Jelajahi produk lain
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent

