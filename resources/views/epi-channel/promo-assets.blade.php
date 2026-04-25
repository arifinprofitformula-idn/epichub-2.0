<x-layouts::app :title="__('Materi Promosi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Materi Promosi"
            description="Kumpulan materi promosi aktif yang bisa kamu gunakan untuk kebutuhan sharing."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        @if (! $hasPromoAssetsTable || $assets->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Materi promosi belum tersedia"
                    description="Materi promosi akan muncul di sini setelah disiapkan oleh admin."
                />
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($assets as $asset)
                    <x-ui.card class="p-6">
                        <div class="text-base font-semibold text-zinc-900 dark:text-white">{{ $asset->title }}</div>

                        @if ($asset->description)
                            <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $asset->description }}</div>
                        @endif

                        <div class="mt-5 flex flex-wrap gap-2">
                            @if ($asset->external_url)
                                <x-ui.button variant="secondary" size="sm" :href="$asset->external_url">
                                    Buka Link
                                </x-ui.button>
                            @endif

                            @if ($asset->file_path)
                                <x-ui.button variant="ghost" size="sm" :href="asset('storage/'.$asset->file_path)">
                                    Unduh File
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
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
