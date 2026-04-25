<x-ui.card class="p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-sm font-semibold text-zinc-900 dark:text-white">EPI Channel belum aktif</div>
            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Status EPI Channel Anda belum aktif. Aktivasi dilakukan melalui OMS/Admin.
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.badge variant="{{ ($channel?->status?->value ?? null) === 'suspended' ? 'danger' : 'warning' }}">
                {{ $channel?->status?->label() ?? 'Belum Aktif' }}
            </x-ui.badge>
            <x-ui.button variant="primary" size="sm" :href="route('dashboard')">
                Kembali ke dashboard
            </x-ui.button>
        </div>
    </div>
</x-ui.card>
