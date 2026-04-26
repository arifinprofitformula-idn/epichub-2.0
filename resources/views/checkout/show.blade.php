<x-layouts::public title="Checkout">
    <section class="mx-auto max-w-[var(--container-6xl)] px-4 py-10">
        <div class="mb-6 flex items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">
                ← Kembali ke produk
            </x-ui.button>

            @auth
                <x-ui.button variant="ghost" size="sm" :href="route('orders.index')">
                    Riwayat order
                </x-ui.button>
            @else
                <x-ui.button variant="ghost" size="sm" :href="route('login')">
                    Masuk
                </x-ui.button>
            @endauth
        </div>

        <form method="POST" action="{{ route('checkout.store', $product->slug) }}" class="grid gap-6 lg:grid-cols-5">
            @csrf

            <div class="space-y-6 lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ auth()->guest() ? 'Informasi Akun & Kontak' : 'Akun yang digunakan' }}
                            </div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                @guest
                                    Lengkapi data berikut untuk membuat akun baru sekaligus melanjutkan checkout produk ini.
                                @else
                                    Checkout akan menggunakan data akun aktif Anda.
                                @endguest
                            </div>
                        </div>

                        @auth
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                Sudah Login
                            </span>
                        @endauth
                    </div>

                    @if ($errors->has('checkout'))
                        <div class="mt-4">
                            <x-ui.alert variant="danger" title="Checkout gagal">
                                {{ $errors->first('checkout') }}
                            </x-ui.alert>
                        </div>
                    @endif

                    @if (! $isEligible)
                        <div class="mt-4">
                            <x-ui.alert variant="warning" title="Belum tersedia">
                                {{ $eligibilityMessage ?? 'Produk ini belum tersedia untuk checkout saat ini.' }}
                            </x-ui.alert>
                        </div>
                    @endif

                    @if (auth()->guest())
                        <div class="mt-6 grid gap-5">
                            <div>
                                <label for="name" class="text-sm font-semibold text-zinc-900 dark:text-white">Nama Lengkap</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    autocomplete="name"
                                    class="mt-2 w-full rounded-[1.1rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:focus:ring-emerald-900/40"
                                    placeholder="Nama lengkap"
                                    required
                                />
                                @error('name')
                                    <div class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="text-sm font-semibold text-zinc-900 dark:text-white">Email</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    class="mt-2 w-full rounded-[1.1rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:focus:ring-emerald-900/40"
                                    placeholder="email@anda.com"
                                    required
                                />
                                @error('email')
                                    <div class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="whatsapp_number" class="text-sm font-semibold text-zinc-900 dark:text-white">WhatsApp</label>
                                <input
                                    id="whatsapp_number"
                                    name="whatsapp_number"
                                    type="text"
                                    value="{{ old('whatsapp_number') }}"
                                    autocomplete="tel"
                                    class="mt-2 w-full rounded-[1.1rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:focus:ring-emerald-900/40"
                                    placeholder="628123456789"
                                    required
                                />
                                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Nomor ini akan dipakai sebagai kontak akun Anda di EPIC Hub.</div>
                                @error('whatsapp_number')
                                    <div class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="password" class="text-sm font-semibold text-zinc-900 dark:text-white">Password Akun Baru</label>
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        autocomplete="new-password"
                                        class="mt-2 w-full rounded-[1.1rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:focus:ring-emerald-900/40"
                                        placeholder="Buat password akun baru"
                                        required
                                    />
                                    @error('password')
                                        <div class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="text-sm font-semibold text-zinc-900 dark:text-white">Konfirmasi Password</label>
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        autocomplete="new-password"
                                        class="mt-2 w-full rounded-[1.1rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:focus:ring-emerald-900/40"
                                        placeholder="Ulangi password"
                                        required
                                    />
                                </div>
                            </div>

                            @if ($errors->has('email') || $errors->has('whatsapp_number'))
                                <div class="rounded-[1.25rem] border border-emerald-200/80 bg-emerald-50/90 p-4 text-sm text-emerald-900">
                                    <div class="font-semibold">Sudah punya akun?</div>
                                    <div class="mt-1 text-emerald-900/75">
                                        Silakan login terlebih dahulu untuk melanjutkan pembelian dengan data yang sudah terdaftar.
                                    </div>
                                    <div class="mt-3">
                                        <x-ui.button variant="ghost" size="sm" :href="route('login')">
                                            Login ke akun saya
                                        </x-ui.button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-6 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Nama Lengkap</div>
                                    <div class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</div>
                                </div>
                                <div>
                                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Email</div>
                                    <div class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->email }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">WhatsApp</div>
                                    <div class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->whatsapp_number ?: 'Belum diisi di profil' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </x-ui.card>

                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Metode Pembayaran</div>
                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                        Transfer bank manual. Admin akan memverifikasi pembayaran setelah bukti diupload.
                    </div>

                    <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 text-sm dark:border-zinc-800">
                        <div class="font-semibold text-zinc-900 dark:text-white">
                            {{ data_get(config('epichub.payments.manual_bank_transfer'), 'bank_name') }}
                        </div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-300">
                            No. Rek: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_number') }}
                        </div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-300">
                            A/N: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_name') }}
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-6">
                    <x-ui.card class="p-6 md:p-8">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Ringkasan Pesanan</div>

                        <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                            <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $product->product_type?->label() ?? $product->product_type }}
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-4">
                                <div class="text-sm text-zinc-600 dark:text-zinc-300">Total</div>
                                <div class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                                    Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-ui.button variant="primary" size="lg" type="submit" :disabled="! $isEligible" class="w-full justify-center">
                                Selesaikan Pesanan
                            </x-ui.button>
                        </div>

                        <div class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">
                            Anda akan diarahkan ke halaman instruksi transfer dan upload bukti pembayaran.
                        </div>

                        <x-referral-info-card
                            :channel="data_get($referralInfo ?? [], 'channel')"
                            :source="data_get($referralInfo ?? [], 'source', 'default_system')"
                            :locked="(bool) data_get($referralInfo ?? [], 'is_locked', false)"
                            class="mt-5"
                        />
                    </x-ui.card>
                </div>
            </div>
        </form>
    </section>
</x-layouts::public>
