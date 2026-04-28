<x-filament-panels::page>
    @php
        /** @var \App\Filament\Resources\LegacyV1Commissions\Pages\ViewLegacyV1Commission $this */
        /** @var \App\Models\LegacyV1Commission $record */
        $record = $this->getRecord();
        $statusColor = $record->commission_status?->getColor() ?? 'gray';
        $migrationColor = $record->migration_status?->getColor() ?? 'gray';
        $badgeClasses = [
            'success' => 'bg-emerald-100 text-emerald-700',
            'info' => 'bg-sky-100 text-sky-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-rose-100 text-rose-700',
            'gray' => 'bg-slate-100 text-slate-700',
        ];
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="text-sm font-medium text-slate-500">Legacy Commission</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">#{{ $record->id }}</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="{{ $badgeClasses[$statusColor] ?? $badgeClasses['gray'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                            {{ $record->commission_status?->label() ?? 'Unknown' }}
                        </span>
                        <span class="{{ $badgeClasses[$migrationColor] ?? $badgeClasses['gray'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                            {{ $record->migration_status?->label() ?? 'Pending' }}
                        </span>
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            EPIC HUB 1.0
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 px-5 py-4 text-right">
                    <div class="text-xs uppercase tracking-[0.16em] text-slate-500">Nominal</div>
                    <div class="mt-2 text-2xl font-semibold text-slate-950">Rp {{ number_format((float) $record->commission_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-950">Identitas</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-slate-500">Legacy Commission ID</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->legacy_commission_id ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Batch</dt>
                        <dd class="mt-1 font-medium text-slate-950">#{{ $record->import_batch_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Legacy User EPIC ID</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->legacy_user_epic_id ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Legacy Email</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->legacy_user_email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">User 2.0</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->user?->name ?? 'Belum terhubung' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">EPI Channel 2.0</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->epiChannel?->epic_code ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-950">Komisi</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-slate-500">Produk Legacy</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->legacy_product_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Produk 2.0</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->product?->title ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Tipe Komisi</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ filled($record->commission_type) ? \Illuminate\Support\Str::headline((string) $record->commission_type) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Level</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->commission_level ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Earned At</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->earned_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Paid At</dt>
                        <dd class="mt-1 font-medium text-slate-950">{{ $record->paid_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-950">Raw Payload</h3>
                <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs text-slate-100">{{ json_encode($record->raw_payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-950">Import Errors</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($record->errors as $error)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold text-slate-950">{{ $error->code }}</div>
                                <div class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ $error->severity }}</div>
                            </div>
                            <div class="mt-2 text-sm text-slate-700">{{ $error->message }}</div>
                            <div class="mt-2 text-xs text-slate-500">
                                Scope: {{ $error->scope }}
                                @if ($error->resolved_at)
                                    · Resolved {{ $error->resolved_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                            Tidak ada import error untuk ledger ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
