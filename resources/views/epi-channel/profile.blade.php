<x-layouts::app :title="__('Profil EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Profil Channel"
            description="Informasi profil EPI Channel yang bersifat read-only untuk MVP."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6">
            <x-ui.card class="p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">EPIC Code</div>
                        <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->epic_code }}</div>
                    </div>
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Store Name</div>
                        <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->store_name ?: '-' }}</div>
                    </div>
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Sponsor EPIC Code</div>
                        <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->sponsor_epic_code ?: '-' }}</div>
                    </div>
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Sponsor Name</div>
                        <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->sponsor_name ?: '-' }}</div>
                    </div>
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Status</div>
                        <div class="mt-2">@include('epi-channel.partials.status-badge', ['status' => $channel->status])</div>
                    </div>
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Source</div>
                        <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->source ?: '-' }}</div>
                    </div>
                    <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-4 dark:border-zinc-800 md:col-span-2">
                        <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Activated At</div>
                        <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->activated_at?->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
