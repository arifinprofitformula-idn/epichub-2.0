<x-layouts::public title="Events">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Events"
            description="Daftar event premium yang sedang dibuka."
        />

        @if ($events->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada event"
                    description="Event akan muncul di sini saat sudah dipublish."
                />
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach ($events as $event)
                    <x-ui.card class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $event->title }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                    @if ($event->starts_at)
                                        {{ $event->starts_at->format('d M Y, H:i') }} {{ $event->timezone }}
                                    @else
                                        Jadwal menyusul
                                    @endif
                                </div>
                                @if (filled($event->speaker_name))
                                    <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                        {{ $event->speaker_name }}@if (filled($event->speaker_title)), {{ $event->speaker_title }}@endif
                                    </div>
                                @endif
                            </div>

                            <x-ui.badge variant="info">
                                {{ $event->status?->label() ?? ($event->status?->value ?? '-') }}
                            </x-ui.badge>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-zinc-600 dark:text-zinc-300">
                            <div>
                                @if ($event->quota !== null)
                                    Sisa kursi: {{ $event->remainingSeats() }}
                                @else
                                    Kuota: Tidak dibatasi
                                @endif
                            </div>
                            <x-ui.button variant="secondary" size="sm" :href="route('events.show', $event)">
                                Lihat detail
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $events->links() }}
            </div>
        @endif
    </section>
</x-layouts::public>

