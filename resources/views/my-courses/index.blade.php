<x-layouts::public title="Kelas Saya">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Kelas saya"
            description="Daftar kelas aktif yang kamu miliki."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('my-products.index')">
                Produk saya
            </x-ui.button>
        </x-ui.section-header>

        @if ($userProducts->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada kelas"
                    description="Setelah pembayaran diverifikasi, kelas akan muncul otomatis di sini."
                >
                    <x-slot:action>
                        <x-ui.button variant="primary" :href="route('catalog.products.index')">
                            Jelajahi produk
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach ($userProducts as $userProduct)
                    @php($course = $userProduct->product?->course)
                    @php($coursePublished = (bool) ($course?->isPublished()))
                    @php($progress = $progressByUserProductId[$userProduct->id] ?? ['percent' => 0, 'completed' => 0, 'total' => 0])

                    <x-ui.card class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $course?->title ?? ($userProduct->product?->title ?? 'Kelas') }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                    {{ $userProduct->product?->title ?? '-' }}
                                </div>
                            </div>

                            @if ($coursePublished)
                                <x-ui.button variant="secondary" size="sm" :href="route('my-courses.show', $userProduct)">
                                    Lanjut belajar
                                </x-ui.button>
                            @else
                                <x-ui.badge variant="warning">Disiapkan</x-ui.badge>
                            @endif
                        </div>

                        <div class="mt-4 grid gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                            <div class="flex items-center justify-between gap-4">
                                <div>Progress</div>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $progress['percent'] }}%
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div>Lesson selesai</div>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $progress['completed'] }} / {{ $progress['total'] }}
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $userProducts->links() }}
            </div>
        @endif
    </section>
</x-layouts::public>

