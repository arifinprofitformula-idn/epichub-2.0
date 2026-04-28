<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Legacy Migration Report</h2>
                    @if ($batch)
                        <p class="mt-2 text-sm text-slate-600">
                            Batch #{{ $batch->id }} · {{ $batch->source_type }} · {{ $batch->status }}
                        </p>
                        <p class="text-sm text-slate-500">{{ $batch->file_name }}</p>
                    @else
                        <p class="mt-2 text-sm text-slate-600">Belum ada batch migrasi legacy.</p>
                    @endif
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Gunakan halaman ini untuk audit hasil import, conflict, fallback sponsor, dan status grant akses.
                </div>
            </div>
        </section>

        @if ($batch)
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($summary as $key => $value)
                    @if (is_scalar($value) && ! in_array($key, ['batch_uuid', 'source_type', 'status', 'generated_at'], true))
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ str_replace('_', ' ', $key) }}</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $value }}</div>
                        </div>
                    @endif
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Recent Errors</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentErrors as $error)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-slate-900">{{ $error->code }}</div>
                                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ $error->severity }}</div>
                                </div>
                                <div class="mt-2 text-sm text-slate-700">{{ $error->message }}</div>
                                <div class="mt-2 text-xs text-slate-500">Scope: {{ $error->scope }}</div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                                Tidak ada error untuk batch ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Recent Batches</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($recentBatches as $recentBatch)
                            <a
                                href="{{ route('filament.admin.pages.legacy-migration-report', ['batch' => $recentBatch->id]) }}"
                                class="block rounded-xl border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold text-slate-900">Batch #{{ $recentBatch->id }}</div>
                                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ $recentBatch->status }}</div>
                                </div>
                                <div class="mt-1 text-sm text-slate-600">{{ $recentBatch->file_name }}</div>
                                <div class="mt-2 text-xs text-slate-500">{{ $recentBatch->source_type }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
