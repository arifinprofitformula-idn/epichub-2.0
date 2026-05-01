<x-layouts::app :title="$client->name.' - Daftar Klien'">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            :title="$client->name"
            description="Profil klien, riwayat order, produk aktif, komisi, dan catatan follow-up."
        >
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button variant="ghost" size="sm" :href="route('dashboard.clients.index')">
                    Kembali ke Klien
                </x-ui.button>
                @if ($client->whatsapp_url)
                    <x-ui.button variant="primary" size="sm" :href="$client->whatsapp_url" target="_blank" rel="noopener noreferrer">
                        WhatsApp Klien
                    </x-ui.button>
                @endif
            </div>
        </x-ui.section-header>

        @if (session('client_notice'))
            <div class="mt-6 rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('client_notice') }}
            </div>
        @endif

        <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
            <x-ui.card class="overflow-hidden p-0">
                <div class="bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(236,254,255,0.9)_48%,rgba(239,246,255,0.96))] p-6 md:p-7">
                    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex size-16 shrink-0 items-center justify-center rounded-[1.5rem] bg-[linear-gradient(135deg,#dbeafe,#cffafe)] text-lg font-semibold text-cyan-700 shadow-[0_12px_28px_rgba(14,165,233,0.14)]">
                                {{ $client->initials() }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Profil Klien</div>
                                <div class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $client->name }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    @include('dashboard.clients.partials.client-status-badges', ['badges' => $client->status_badges])
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.25rem] border border-white/70 bg-white/80 px-4 py-3 text-sm shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Follow Up Terbaru</div>
                            <div class="mt-2">
                                @include('dashboard.clients.partials.follow-up-status-badge', ['followUpStatus' => $client->latest_note?->follow_up_status])
                            </div>
                            <div class="mt-3 text-sm text-slate-500">
                                {{ $client->latest_note?->created_at?->translatedFormat('d M Y H:i') ?? 'Belum ada aktivitas' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-[1.25rem] border border-slate-200 bg-white/80 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tanggal Daftar</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $client->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="rounded-[1.25rem] border border-slate-200 bg-white/80 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total Order Paid</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ number_format((int) $client->paid_orders_count) }}</div>
                        </div>
                        <div class="rounded-[1.25rem] border border-slate-200 bg-white/80 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total Omzet</div>
                            <div class="mt-2 font-semibold text-slate-900">Rp {{ number_format((float) $client->paid_orders_total_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-[1.25rem] border border-slate-200 bg-white/80 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Produk Terakhir</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $client->latest_product_label ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="text-sm font-semibold text-slate-900">Kontak Klien</div>
                <div class="mt-5 space-y-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Email</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $client->email }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">WhatsApp</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $client->whatsapp_number ?: 'Belum ada nomor' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Referrer Lock</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $client->referrerEpiChannel?->epic_code ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Next Follow Up</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $client->latest_note?->next_follow_up_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <x-ui.card class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Riwayat Order</div>
                        <div class="mt-1 text-sm text-slate-500">Order paid yang teratribusi ke channel Anda.</div>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($orderHistory as $order)
                        <div class="rounded-[1.25rem] border border-slate-200 p-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900">{{ $order->order_number }}</div>
                                    <div class="mt-1 text-sm text-slate-500">{{ $order->paid_at?->translatedFormat('d M Y H:i') ?? $order->created_at?->translatedFormat('d M Y H:i') }}</div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($order->items as $item)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                {{ $item->product?->title ?? $item->product_title }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-slate-900">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $order->status?->label() ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="Belum ada order paid"
                            description="Riwayat order klien akan muncul setelah ada pembelian yang valid."
                        />
                    @endforelse
                </div>
            </x-ui.card>

            <div class="grid gap-5">
                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-slate-900">Produk Dimiliki</div>
                    <div class="mt-5 space-y-3">
                        @forelse ($userProducts as $userProduct)
                            <div class="rounded-[1.25rem] border border-slate-200 p-4">
                                <div class="font-semibold text-slate-900">{{ $userProduct->product?->title ?? 'Produk tidak ditemukan' }}</div>
                                <div class="mt-1 text-sm text-slate-500">
                                    {{ $userProduct->status?->label() ?? '-' }}
                                    · Granted {{ $userProduct->granted_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada produk"
                                description="Produk milik klien akan muncul setelah akses diberikan."
                            />
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-slate-900">Komisi dari Klien Ini</div>
                    <div class="mt-5 space-y-3">
                        @forelse ($commissions as $commission)
                            <div class="rounded-[1.25rem] border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $commission->product?->title ?? 'Komisi' }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $commission->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-slate-900">Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $commission->status?->label() ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada komisi"
                                description="Komisi akan tampil jika order klien menghasilkan komisi untuk channel Anda."
                            />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(380px,0.9fr)]">
            <x-ui.card class="p-6">
                <div class="text-sm font-semibold text-slate-900">Catatan Follow Up</div>
                <div class="mt-5 space-y-3">
                    @if ($notesEnabled)
                        @forelse ($notes as $note)
                            <div class="rounded-[1.25rem] border border-slate-200 p-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div class="min-w-0">
                                        <div class="text-sm text-slate-700">{{ $note->note }}</div>
                                        <div class="mt-3 text-xs text-slate-500">
                                            {{ $note->creator?->name ?: 'System' }} · {{ $note->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-start gap-2 md:items-end">
                                        @include('dashboard.clients.partials.follow-up-status-badge', ['followUpStatus' => $note->follow_up_status])
                                        @if ($note->next_follow_up_at)
                                            <div class="text-xs text-slate-500">
                                                Next: {{ $note->next_follow_up_at->translatedFormat('d M Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada catatan"
                                description="Tambahkan catatan follow-up pertama untuk klien ini."
                            />
                        @endforelse
                    @else
                        <x-ui.empty-state
                            title="Catatan belum aktif"
                            description="Jalankan migration affiliate_client_notes agar catatan follow-up tersedia."
                        />
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card class="p-6" id="catatan-baru">
                <div class="text-sm font-semibold text-slate-900">Tambah Catatan</div>
                <div class="mt-1 text-sm text-slate-500">Simpan hasil follow-up, status, dan agenda kontak berikutnya.</div>

                @if ($notesEnabled)
                    <form method="POST" action="{{ route('dashboard.clients.notes.store', $client) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="note" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Catatan</label>
                            <textarea
                                id="note"
                                name="note"
                                rows="5"
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                                placeholder="Tulis hasil follow-up, kebutuhan klien, atau langkah berikutnya..."
                            >{{ old('note') }}</textarea>
                            @error('note')
                                <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="follow_up_status" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Status Follow Up</label>
                                <select id="follow_up_status" name="follow_up_status" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                    <option value="">Pilih status</option>
                                    @foreach ($followUpStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('follow_up_status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('follow_up_status')
                                    <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="next_follow_up_at" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Next Follow Up</label>
                                <input
                                    id="next_follow_up_at"
                                    type="datetime-local"
                                    name="next_follow_up_at"
                                    value="{{ old('next_follow_up_at') }}"
                                    class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                                >
                                @error('next_follow_up_at')
                                    <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <x-ui.button variant="primary" size="md" type="submit">
                                Simpan Catatan
                            </x-ui.button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('dashboard.clients.follow-up.store', $client) }}" class="mt-3">
                        @csrf
                        <x-ui.button variant="secondary" size="md" type="submit">
                            Tandai Sudah Follow Up
                        </x-ui.button>
                    </form>
                @endif
            </x-ui.card>
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
