<x-layouts::public title="Event Saya">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Event saya"
            description="Daftar event yang kamu ikuti."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('events.index')">
                Lihat event
            </x-ui.button>
        </x-ui.section-header>

        @if ($registrations->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada event"
                    description="Setelah pembayaran diverifikasi, event akan muncul otomatis di sini."
                >
                    <x-slot:action>
                        <x-ui.button variant="primary" :href="route('events.index')">
                            Lihat event
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach ($registrations as $registration)
                    @php($event = $registration->event)

                    <x-ui.card class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $event?->title ?? 'Event' }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                    @if ($event?->starts_at)
                                        {{ $event->starts_at->format('d M Y, H:i') }} {{ $event->timezone }}
                                    @else
                                        Jadwal menyusul
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                                    <x-ui.badge variant="info">
                                        {{ $event?->status?->label() ?? ($event?->status?->value ?? '-') }}
                                    </x-ui.badge>
                                    <x-ui.badge variant="neutral">
                                        {{ $registration->status?->label() ?? ($registration->status?->value ?? '-') }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <x-ui.button variant="secondary" size="sm" :href="route('my-events.show', $registration)">
                                Lihat detail
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $registrations->links() }}
            </div>
        @endif
    </section>
</x-layouts::public>

