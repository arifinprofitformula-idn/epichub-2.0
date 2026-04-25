<x-layouts::public :title="$event->title">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="rounded-[2rem] border border-slate-200 bg-white/92 p-6 shadow-[0_20px_45px_rgba(15,23,42,0.08)] md:p-8">
            <div class="mb-6">
                <x-ui.button variant="ghost" size="sm" :href="route('events.index')">
                    ← Kembali
                </x-ui.button>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $event->title }}
                            </div>
                            <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                @if ($event->starts_at)
                                    {{ $event->starts_at->format('d M Y, H:i') }} {{ $event->timezone }}
                                    @if ($event->ends_at)
                                        — {{ $event->ends_at->format('d M Y, H:i') }}
                                    @endif
                                @else
                                    Jadwal menyusul
                                @endif
                            </div>
                        </div>
                        <x-ui.badge variant="info">
                            {{ $event->status?->label() ?? ($event->status?->value ?? '-') }}
                        </x-ui.badge>
                    </div>

                    <div class="mt-6 grid gap-3 text-sm">
                        @if (filled($event->speaker_name))
                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                <div class="text-xs text-zinc-600 dark:text-zinc-300">Speaker</div>
                                <div class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                    {{ $event->speaker_name }}
                                </div>
                                @if (filled($event->speaker_title))
                                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $event->speaker_title }}
                                    </div>
                                @endif
                                @if (filled($event->speaker_bio))
                                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $event->speaker_bio }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                            <div class="text-xs text-zinc-600 dark:text-zinc-300">Kuota</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                @if ($event->quota !== null)
                                    {{ $event->remainingSeats() }} kursi tersisa
                                @else
                                    Tidak dibatasi
                                @endif
                            </div>
                        </div>

                        @if (filled($event->description))
                            <div class="prose prose-zinc max-w-none dark:prose-invert">
                                {!! $event->description !!}
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Tiket</div>
                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                        Untuk ikut event ini, silakan daftar melalui pembelian tiket.
                    </div>

                    @php($product = $event->product)
                    @php($productPublished = (bool) ($product?->status?->value === 'published' && $product?->visibility?->value === 'public' && ($product?->publish_at === null || $product?->publish_at?->isPast())))
                    @php($eventFull = (bool) ($event->quota !== null && $event->remainingSeats() === 0))
                    @php($eventClosed = in_array($event->status?->value, ['closed', 'completed'], true))

                    <div class="mt-6">
                        @if (! $product || ! $productPublished)
                            <x-ui.alert variant="info" title="Catatan">
                                Tiket belum tersedia.
                            </x-ui.alert>
                        @elseif ($eventFull)
                            <x-ui.badge variant="warning">Kuota penuh</x-ui.badge>
                        @elseif ($eventClosed)
                            <x-ui.badge variant="neutral">Pendaftaran ditutup</x-ui.badge>
                        @else
                            <x-ui.button variant="primary" :href="route('checkout.show', $product)">
                                Daftar / Beli tiket
                            </x-ui.button>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        </div>
        </div>
    </section>
</x-layouts::public>

