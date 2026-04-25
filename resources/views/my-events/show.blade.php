<x-layouts::public :title="$event->title">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('my-events.index')">
                ← Kembali
            </x-ui.button>
            <x-ui.badge variant="neutral">
                {{ $registration->status?->label() ?? ($registration->status?->value ?? '-') }}
            </x-ui.badge>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
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
                            </div>
                        @endif

                        <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                            <div class="text-xs text-zinc-600 dark:text-zinc-300">Status event</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                {{ $event->status?->label() ?? ($event->status?->value ?? '-') }}
                            </div>
                        </div>

                        @if (filled($event->zoom_meeting_id) || filled($event->zoom_passcode))
                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                <div class="text-xs text-zinc-600 dark:text-zinc-300">Info Zoom</div>
                                <div class="mt-2 grid gap-2 text-sm">
                                    @if (filled($event->zoom_meeting_id))
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="text-zinc-600 dark:text-zinc-300">Meeting ID</div>
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $event->zoom_meeting_id }}</div>
                                        </div>
                                    @endif
                                    @if (filled($event->zoom_passcode))
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="text-zinc-600 dark:text-zinc-300">Passcode</div>
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $event->zoom_passcode }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Aksi</div>
                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                        Akses Zoom dan replay hanya tersedia untuk registrasi yang valid.
                    </div>

                    <div class="mt-6 grid gap-2">
                        @if ($canJoin)
                            <x-ui.button variant="primary" :href="route('my-events.join', $registration)">
                                Masuk Zoom
                            </x-ui.button>
                        @else
                            <x-ui.button variant="secondary" disabled>
                                Masuk Zoom
                            </x-ui.button>
                        @endif

                        @if ($canViewReplay)
                            <x-ui.button variant="secondary" :href="route('my-events.replay', $registration)">
                                Buka Replay
                            </x-ui.button>
                        @else
                            <x-ui.button variant="secondary" disabled>
                                Buka Replay
                            </x-ui.button>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        </div>
    </section>
</x-layouts::public>

