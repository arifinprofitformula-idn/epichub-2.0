@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if (filled($eyebrow))
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                {{ $eyebrow }}
            </div>
        @endif
        @if (filled($title))
            <div class="mt-1 text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                {{ $title }}
            </div>
        @endif
        @if (filled($description))
            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $description }}
            </div>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="mt-3 shrink-0 sm:mt-0">
            {{ $slot }}
        </div>
    @endif
</div>
