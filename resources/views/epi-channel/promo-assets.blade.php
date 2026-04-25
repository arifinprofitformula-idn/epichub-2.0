<x-layouts::public title="Materi Promosi">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Materi promosi"
            description="Asset promosi yang bisa kamu gunakan untuk sharing."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.index')">
                Dashboard penghasilan
            </x-ui.button>
        </x-ui.section-header>

        @if (! $channel || ! $channel->isActive())
            <div class="mt-6">
                <x-ui.empty-state
                    title="EPI Channel belum aktif"
                    description="Aktivasi dilakukan melalui OMS atau admin."
                />
            </div>
        @else
            @if ($assets->count() === 0)
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Belum ada materi"
                        description="Materi promosi akan muncul di sini."
                    />
                </div>
            @else
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($assets as $asset)
                        <x-ui.card class="p-6">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $asset->title }}</div>
                            @if ($asset->description)
                                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $asset->description }}</div>
                            @endif

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($asset->external_url)
                                    <x-ui.button variant="secondary" size="sm" :href="$asset->external_url">
                                        Buka link
                                    </x-ui.button>
                                @endif
                                @if ($asset->file_path)
                                    <x-ui.button variant="ghost" size="sm" :href="asset('storage/'.$asset->file_path)">
                                        Unduh file
                                    </x-ui.button>
                                @endif
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $assets->links() }}
                </div>
            @endif
        @endif
    </section>
</x-layouts::public>

