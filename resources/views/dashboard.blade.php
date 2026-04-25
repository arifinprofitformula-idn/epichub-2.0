<x-layouts::app :title="__('Dashboard')">
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        <section class="sticky top-0 z-20 mb-[10px] hidden flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-1 py-5 backdrop-blur md:-mt-8 md:-mx-6 md:px-0 md:flex lg:-mx-8">
            <div class="flex items-center gap-3 md:pl-6 lg:pl-8">
                <flux:sidebar.toggle
                    class="hidden lg:inline-flex size-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700"
                    icon="bars-2"
                    inset="left"
                />
            </div>

            <div class="flex items-center gap-4 md:pr-6 lg:pr-8">
                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-900">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="mt-0.5 text-xs font-medium text-slate-500">
                        {{ auth()->user()->hasVerifiedEmail() ? 'Pengguna terverifikasi' : 'Menunggu verifikasi' }}
                    </div>
                </div>

                <a
                    href="{{ route('profile.edit') }}"
                    class="group inline-flex size-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a,#1d4ed8)] text-sm font-semibold text-white shadow-[0_12px_25px_rgba(37,99,235,0.18)] transition hover:brightness-110"
                    aria-label="Buka profil pengguna"
                >
                    <span class="group-hover:scale-105 transition">
                        {{ auth()->user()->initials() }}
                    </span>
                </a>
            </div>
        </section>

        <div class="pt-[20px]">
            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(236,254,255,0.88)_48%,rgba(255,248,225,0.84))] shadow-[0_20px_55px_rgba(15,23,42,0.08)]">
                <div class="grid gap-6 p-6 md:grid-cols-[minmax(0,1fr)_210px] md:items-center md:p-8">
                    <div>
                        <x-ui.badge variant="info">Dashboard Belajar</x-ui.badge>
                        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 md:text-5xl">
                            Halo, {{ auth()->user()->name }}! <span class="align-middle text-2xl md:text-4xl">👋</span>
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600 md:text-lg">
                            Ayo lanjutkan belajarmu hari ini dan temukan program LMS yang paling relevan untuk level berikutnya.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('my-products.index') }}" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700">
                                Lihat Semua Akses
                            </a>
                            <a href="{{ route('marketplace.index') }}" class="inline-flex items-center rounded-full border border-transparent bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-[0_10px_25px_rgba(245,158,11,0.25)] transition hover:brightness-95">
                                Marketplace
                            </a>
                        </div>
                    </div>

                    <div class="justify-self-start md:justify-self-end">
                        <div class="min-w-[170px] rounded-[1.5rem] border border-cyan-100 bg-cyan-50/80 p-5 text-center shadow-[0_10px_30px_rgba(34,211,238,0.12)]">
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-700">
                                Kursus Aktif
                            </div>
                            <div class="mt-3 text-4xl font-semibold tracking-tight text-slate-900">
                                {{ $activeCoursesCount }}
                            </div>
                            <div class="mt-2 text-xs text-slate-500">
                                {{ $activeUserProductsCount }} akses produk aktif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="mt-10 grid gap-8 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,1fr)]">
            <div id="kursus-saya">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <div class="text-3xl font-semibold tracking-tight text-slate-900">Kursus Saya</div>
                        <p class="mt-2 text-sm text-slate-500">Akses kelas yang sudah kamu miliki dan lanjutkan progress belajar tanpa ribet.</p>
                    </div>
                    <x-ui.button variant="ghost" size="sm" :href="route('my-courses.index')">
                        Semua kelas
                    </x-ui.button>
                </div>

                @if ($activeCourseUserProducts->isEmpty())
                    <x-ui.card class="mt-6 p-8">
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center">
                            <div class="text-sm font-semibold text-slate-900">Belum ada kelas aktif</div>
                            <div class="mt-2 text-sm text-slate-500">Begitu kamu membeli produk LMS, kelas akan langsung tampil di area ini.</div>
                            <div class="mt-5">
                                <x-ui.button variant="primary" size="md" :href="route('catalog.products.index', ['type' => 'course'])">
                                    Lihat katalog LMS
                                </x-ui.button>
                            </div>
                        </div>
                    </x-ui.card>
                @else
                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        @foreach ($activeCourseUserProducts as $userProduct)
                            @php($product = $userProduct->product)
                            @php($course = $product?->course)
                            @php($progress = $progressByUserProductId[$userProduct->id] ?? ['percent' => 0, 'completed' => 0, 'total' => 0])
                            @php($actionLabel = $progress['percent'] > 0 ? 'Lanjutkan Belajar' : 'Mulai Belajar')

                            <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_20px_40px_rgba(15,23,42,0.06)]">
                                <div class="relative aspect-[16/10] bg-slate-100">
                                    @if (filled($course?->thumbnail ?? $product?->thumbnail))
                                        <img
                                            src="{{ asset('storage/'.($course?->thumbnail ?? $product?->thumbnail)) }}"
                                            alt="{{ $course?->title ?? $product?->title }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.28),_transparent_45%),linear-gradient(135deg,#0f172a,#1e293b_45%,#0f766e)]"></div>
                                    @endif

                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent px-6 pb-6 pt-16 text-white">
                                        <div class="flex items-start justify-between gap-3">
                                            <x-ui.badge variant="success">
                                                {{ $userProduct->expires_at ? 'Aktif Sampai '.$userProduct->expires_at->translatedFormat('d M Y') : 'Lifetime Access' }}
                                            </x-ui.badge>
                                            <div class="rounded-full bg-white/18 px-3 py-1 text-xs font-semibold backdrop-blur">
                                                {{ $progress['completed'] }}/{{ $progress['total'] }} materi
                                            </div>
                                        </div>

                                        <div class="mt-4 text-2xl font-semibold tracking-tight">
                                            {{ $course?->title ?? $product?->title ?? 'Kelas Premium' }}
                                        </div>
                                        <p class="mt-2 text-sm text-white/80">
                                            {{ $course?->short_description ?? $product?->short_description ?? 'Kelas siap kamu lanjutkan kapan saja.' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <div class="flex items-center justify-between gap-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                        <span>Progress Belajar</span>
                                        <span class="text-cyan-600">{{ $progress['percent'] }}%</span>
                                    </div>
                                    <div class="mt-3 h-2 rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-[linear-gradient(90deg,#06b6d4,#f59e0b)]" style="width: {{ max(6, $progress['percent']) }}%"></div>
                                    </div>

                                    <div class="mt-5 rounded-[1.25rem] border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="font-medium text-slate-500">Masa aktif</span>
                                            <span class="font-semibold text-slate-900">
                                                {{ $userProduct->expires_at ? $userProduct->expires_at->translatedFormat('d M Y') : 'Selamanya' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-5 flex flex-col gap-3">
                                        <a
                                            href="{{ route('my-courses.show', $userProduct) }}"
                                            class="inline-flex items-center justify-center rounded-[1rem] bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-5 py-3 text-sm font-semibold text-white shadow-[0_15px_30px_rgba(37,99,235,0.22)] transition hover:brightness-105"
                                        >
                                            {{ $actionLabel }}
                                        </a>

                                        <div class="text-center text-xs text-slate-400">
                                            {{ $progress['percent'] >= 100 ? 'Seluruh materi utama sudah selesai dipelajari.' : 'Progress akan bertambah setiap materi selesai dibuka dan ditandai selesai.' }}
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <div id="katalog-lms">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <div class="text-3xl font-semibold tracking-tight text-slate-900">Katalog</div>
                        <p class="mt-2 text-sm text-slate-500">Rekomendasi produk LMS yang bisa dibeli atau langsung diakses jika sudah kamu miliki.</p>
                    </div>
                    <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.index', ['type' => 'course'])">
                        Lihat semua
                    </x-ui.button>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($catalogCourses as $catalogItem)
                        @php($product = $catalogItem['product'])
                        @php($course = $product->course)
                        @php($ownedUserProduct = $catalogItem['ownedUserProduct'])
                        @php($progress = $catalogItem['progress'])
                        @php($owned = $ownedUserProduct !== null)

                        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-[0_16px_35px_rgba(15,23,42,0.05)]">
                            <div class="flex gap-4">
                                <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-[1.25rem] bg-slate-100">
                                    @if (filled($course?->thumbnail ?? $product->thumbnail))
                                        <img
                                            src="{{ asset('storage/'.($course?->thumbnail ?? $product->thumbnail)) }}"
                                            alt="{{ $course?->title ?? $product->title }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.3),_transparent_45%),linear-gradient(135deg,#0f172a,#164e63)]"></div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge variant="{{ $owned ? 'info' : 'warning' }}">
                                            {{ $owned ? 'Sudah Dibeli' : 'Belum Dibeli' }}
                                        </x-ui.badge>
                                        @if ($product->has_discount)
                                            <x-ui.badge variant="danger">Promo</x-ui.badge>
                                        @endif
                                    </div>

                                    <div class="mt-3 text-base font-semibold tracking-tight text-slate-900">
                                        {{ $course?->title ?? $product->title }}
                                    </div>
                                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">
                                        {{ $course?->short_description ?? $product->short_description ?? 'Kelas premium untuk membantu progress belajarmu lebih terarah.' }}
                                    </p>

                                    <div class="mt-4">
                                        @if ($owned)
                                            <div class="flex items-center justify-between gap-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                                <span>Progress</span>
                                                <span class="text-cyan-600">{{ $progress['percent'] }}%</span>
                                            </div>
                                            <div class="mt-2 h-2 rounded-full bg-slate-100">
                                                <div class="h-2 rounded-full bg-[linear-gradient(90deg,#06b6d4,#2563eb)]" style="width: {{ max(6, $progress['percent']) }}%"></div>
                                            </div>
                                        @else
                                            <div class="flex items-end justify-between gap-4">
                                                <div>
                                                    @if ($product->has_discount)
                                                        <div class="text-xs text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</div>
                                                    @endif
                                                    <div class="text-lg font-semibold tracking-tight text-slate-900">
                                                        Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                                <div class="text-xs text-slate-400">
                                                    {{ $product->category?->name ?? 'LMS' }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                @if ($owned)
                                    <a
                                        href="{{ route('my-courses.show', $ownedUserProduct) }}"
                                        class="inline-flex w-full items-center justify-center rounded-[1rem] bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-4 py-3 text-sm font-semibold text-white shadow-[0_15px_30px_rgba(37,99,235,0.18)] transition hover:brightness-105"
                                    >
                                        Akses Kelas
                                    </a>
                                @else
                                    <a
                                        href="{{ route('checkout.show', $product->slug) }}"
                                        class="inline-flex w-full items-center justify-center rounded-[1rem] bg-[linear-gradient(135deg,#f59e0b,#f97316)] px-4 py-3 text-sm font-semibold text-slate-950 shadow-[0_15px_30px_rgba(245,158,11,0.2)] transition hover:brightness-95"
                                    >
                                        Beli Sekarang
                                    </a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <x-ui.card class="p-8">
                            <div class="rounded-[1.25rem] border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center">
                                <div class="text-sm font-semibold text-slate-900">Katalog LMS segera hadir</div>
                                <div class="mt-2 text-sm text-slate-500">Saat produk course dipublish, daftar katalog akan tampil di area ini.</div>
                            </div>
                        </x-ui.card>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.card class="p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Event</div>
                <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $activeEventsCount }}</div>
                <p class="mt-2 text-sm text-slate-500">Event yang sedang atau pernah kamu ikuti dari akun ini.</p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Produk Aktif</div>
                <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $activeUserProductsCount }}</div>
                <p class="mt-2 text-sm text-slate-500">Semua akses produk yang masih aktif di dashboard kamu.</p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">EPI Channel</div>
                <div class="mt-3 text-lg font-semibold tracking-tight text-slate-900">{{ $epiChannelStatus }}</div>
                <p class="mt-2 text-sm text-slate-500">{{ $epiChannelDescription }}</p>
                <div class="mt-4">
                    <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                        {{ $epiChannel?->isActive() ? 'Dashboard Penghasilan' : 'Status EPI Channel' }}
                    </x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Marketplace</div>
                <div class="mt-3 text-lg font-semibold tracking-tight text-slate-900">Jelajahi Produk</div>
                <p class="mt-2 text-sm text-slate-500">Temukan produk digital, kelas, event, dan bundle pilihan EPIC Hub.</p>
                <div class="mt-4">
                    <x-ui.button variant="ghost" size="sm" :href="route('marketplace.index')">
                        Buka Marketplace
                    </x-ui.button>
                </div>
            </x-ui.card>
        </section>

        <footer class="mt-auto flex flex-col gap-3 border-t border-slate-200/80 px-1 pt-6 pb-0 text-sm text-slate-500 md:flex-row md:items-center md:justify-between md:pb-0">
            <div>© 2026 EPIC Hub</div>

            <div class="flex items-center gap-3">
                <flux:modal.trigger name="dashboard-terms-of-service">
                    <button type="button" class="transition hover:text-slate-900">
                        Terms of Service
                    </button>
                </flux:modal.trigger>

                <span class="text-slate-300">|</span>

                <flux:modal.trigger name="dashboard-privacy-policy">
                    <button type="button" class="transition hover:text-slate-900">
                        Privacy
                    </button>
                </flux:modal.trigger>
            </div>
        </footer>
    </div>

    <flux:modal name="dashboard-terms-of-service" class="max-w-3xl">
        <div class="space-y-5">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Terms of Service</h2>
                <p class="mt-2 text-sm text-slate-500">Template syarat dan ketentuan penggunaan platform EPIC Hub.</p>
            </div>

            <div class="space-y-4 text-sm leading-relaxed text-slate-600">
                <p>Dengan menggunakan EPIC Hub, pengguna setuju untuk memakai platform ini secara sah, wajar, dan tidak melanggar hukum maupun hak pihak lain.</p>
                <p>Akses terhadap produk digital, kelas, event, dan fitur lain diberikan sesuai jenis pembelian, entitlement, atau persetujuan admin yang berlaku pada akun pengguna.</p>
                <p>Pengguna dilarang mendistribusikan ulang materi, membagikan akses akun, mencoba mengganggu sistem, atau menggunakan platform untuk aktivitas yang merugikan EPIC Hub maupun pengguna lain.</p>
                <p>EPIC Hub berhak memperbarui fitur, kebijakan, harga, maupun ketentuan layanan dari waktu ke waktu untuk menjaga kualitas layanan dan keamanan sistem.</p>
                <p>Apabila ditemukan penyalahgunaan, EPIC Hub dapat membatasi akses, menangguhkan akun, atau mengambil tindakan administratif lain sesuai kebijakan internal.</p>
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-[1rem] bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Tutup
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="dashboard-privacy-policy" class="max-w-3xl">
        <div class="space-y-5">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Privacy Policy</h2>
                <p class="mt-2 text-sm text-slate-500">Template kebijakan privasi untuk penggunaan EPIC Hub.</p>
            </div>

            <div class="space-y-4 text-sm leading-relaxed text-slate-600">
                <p>EPIC Hub dapat mengumpulkan data dasar pengguna seperti nama, email, aktivitas pembelian, progress belajar, dan data teknis yang diperlukan untuk menjalankan layanan.</p>
                <p>Data digunakan untuk autentikasi, pemberian akses produk, pengalaman belajar yang lebih baik, dukungan pengguna, analitik operasional, dan kebutuhan administratif platform.</p>
                <p>Data pengguna tidak dibagikan secara sembarangan kepada pihak lain di luar kebutuhan layanan, kepatuhan hukum, atau integrasi sistem yang memang diperlukan untuk operasional.</p>
                <p>EPIC Hub berupaya menjaga keamanan data dengan kontrol akses, validasi sistem, dan praktik pengelolaan data yang wajar sesuai kebutuhan aplikasi.</p>
                <p>Pengguna dapat menghubungi admin atau pengelola platform untuk permintaan pembaruan data, pertanyaan privasi, atau klarifikasi terkait kebijakan ini.</p>
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-[1rem] bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Tutup
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <x-ui.mobile-bottom-nav />
</x-layouts::app>
