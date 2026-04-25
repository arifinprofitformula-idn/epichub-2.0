@props([
    'title' => null,
    'description' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

<x-ui.card {{ $attributes->merge(['class' => 'p-6']) }}>
    <div class="flex items-start gap-4">
        <div class="flex size-10 items-center justify-center rounded-[var(--radius-lg)] bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2.75c5.109 0 9.25 4.141 9.25 9.25S17.109 21.25 12 21.25 2.75 17.109 2.75 12 6.891 2.75 12 2.75Z" stroke="currentColor" stroke-width="1.5" />
                    <path d="M8.5 12.5h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            @if (filled($title))
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</div>
            @endif
            @if (filled($description))
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $description }}</div>
            @endif

            @if (filled($actionLabel) && filled($actionHref))
                <div class="mt-4">
                    <x-ui.button variant="primary" size="sm" :href="$actionHref">
                        {{ $actionLabel }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </div>
</x-ui.card>
