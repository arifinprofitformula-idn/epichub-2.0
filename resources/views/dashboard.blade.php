<x-layouts::app :title="__('Dashboard')">
    <div id="top" class="mx-auto w-full max-w-[var(--container-5xl)] px-4 pb-24 pt-8 md:pb-10">
        <div class="rounded-[var(--radius-2xl)] border border-zinc-200/70 bg-white p-6 shadow-[var(--shadow-soft)] dark:border-zinc-800 dark:bg-zinc-900 md:p-8">
            <div class="grid gap-8 md:grid-cols-5 md:items-start">
                <div class="md:col-span-3">
                    <x-ui.badge variant="success">Akun aktif</x-ui.badge>
                    <h1 class="mt-4 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white md:text-3xl">
                        Selamat datang kembali, {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
                        Kelola akses produk digital, kelas, event, dan aktivitas Anda dari satu tempat.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button variant="primary" size="md" href="#produk-saya">
                            Lihat Produk Saya
                        </x-ui.button>
                        <x-ui.button variant="ghost" size="md" :href="route('home')">
                            Jelajahi Program
                        </x-ui.button>
                    </div>

                    <div class="mt-6">
                        @if (! auth()->user()->hasVerifiedEmail())
                            <x-ui.alert variant="warning" title="Email belum terverifikasi">
                                Cek inbox email kamu untuk verifikasi. Setelah verifikasi, beberapa fitur akan lebih lancar digunakan.
                            </x-ui.alert>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="grid gap-4">
                        <x-ui.stat-card label="Produk Dimiliki" :value="$activeUserProductsCount" description="Akses aktif">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.75 7.75h14.5M6.75 7.75V6.5c0-1.519 1.231-2.75 2.75-2.75h5c1.519 0 2.75 1.231 2.75 2.75v1.25M7.25 7.75l.9 12h7.7l.9-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </x-ui.stat-card>

                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-2">
                            <x-ui.stat-card label="Kelas Aktif" :value="$activeCoursesCount" description="Akses aktif" />
                            <x-ui.stat-card label="Event Terdaftar" :value="$activeEventsCount" description="Terdaftar" />
                        </div>

                        <x-ui.stat-card label="Status EPI Channel" :value="$epiChannelStatus" :description="$epiChannelDescription">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.5 16.5l2.5-2.5 3 3 4.5-6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4.75 4.75h14.5v14.5H4.75V4.75Z" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </x-ui.stat-card>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
            <x-ui.card class="p-6" id="produk-saya">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Produk Saya</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Akses produk digital yang sudah kamu miliki.</div>
                    </div>
                    <x-ui.button variant="secondary" size="sm" :href="route('my-products.index')">Buka</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6" id="kelas">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Kelas Saya</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Lanjutkan kelas dan progres belajarmu.</div>
                    </div>
                    <x-ui.button variant="secondary" size="sm" :href="route('my-courses.index')">Buka</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6" id="event">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Event Saya</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Lihat jadwal event dan akses link join.</div>
                    </div>
                    <x-ui.button variant="secondary" size="sm" :href="route('my-events.index')">Buka</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6" id="epi-channel">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Program EPI Channel</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Peluang penghasilan dari promosi program.</div>
                    </div>
                    @if ($epiChannel && $epiChannel->isActive())
                        <x-ui.button variant="secondary" size="sm" :href="route('epi-channel.index')">Buka</x-ui.button>
                    @else
                        <x-ui.badge variant="warning">Belum aktif</x-ui.badge>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <div class="mt-8">
            <x-ui.empty-state
                title="Belum ada produk yang dimiliki"
                description="Setelah melakukan pembelian, akses produk akan muncul otomatis di sini."
                action-label="Jelajahi Produk"
                :action-href="route('home').'#produk'"
            />
        </div>

        <div class="mt-8">
            <x-ui.card class="p-6 md:p-8">
                <x-ui.section-header
                    eyebrow="Langkah berikutnya"
                    title="Mulai dari yang paling penting"
                    description="Beberapa langkah sederhana untuk memaksimalkan pengalamanmu di EPIC Hub Premium."
                />

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-5 dark:border-zinc-800">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Lengkapi profil</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Biar pengalamanmu lebih personal dan rapi.</div>
                        <div class="mt-4">
                            <x-ui.button variant="ghost" size="sm" :href="route('profile.edit')">Buka Profil</x-ui.button>
                        </div>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-5 dark:border-zinc-800">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Jelajahi produk digital</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Pilih materi yang relevan dan mulai bertumbuh.</div>
                        <div class="mt-4">
                            <x-ui.button variant="ghost" size="sm" :href="route('home').'#produk'">Lihat Placeholder</x-ui.button>
                        </div>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-5 dark:border-zinc-800">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Ikuti event premium</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Nantinya kamu bisa daftar event langsung dari sini.</div>
                        <div class="mt-4">
                            <x-ui.button variant="ghost" size="sm" :href="route('home').'#event'">Cek Event</x-ui.button>
                        </div>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-5 dark:border-zinc-800">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Aktifkan peluang EPI Channel</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Akan dibuka bertahap, tetap simple dan transparan.</div>
                        <div class="mt-4">
                            <x-ui.button variant="ghost" size="sm" :href="route('home').'#epi-channel'">Lihat Teaser</x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <x-ui.mobile-bottom-nav />
</x-layouts::app>
