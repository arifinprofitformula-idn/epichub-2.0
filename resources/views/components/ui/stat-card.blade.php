@props([
    'label' => null,
    'value' => null,
    'description' => null,
])

<x-ui.card {{ $attributes->merge(['class' => 'p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            @if (filled($label))
                <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
            @endif
            @if (filled($value))
                <div class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $value }}</div>
            @endif
            @if (filled($description))
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $description }}</div>
            @endif
        </div>

        @if ($slot->isNotEmpty())
            <div class="flex size-10 items-center justify-center rounded-[var(--radius-lg)] bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                {{ $slot }}
            </div>
        @endif
    </div>
</x-ui.card>
