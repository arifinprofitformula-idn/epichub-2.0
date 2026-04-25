@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $base = 'rounded-[var(--radius-xl)] border p-4';

    $variants = match ($variant) {
        'success' => 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-100',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-100',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-950 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-100',
        default => 'border-cyan-200 bg-cyan-50 text-cyan-950 dark:border-cyan-900/60 dark:bg-cyan-950/40 dark:text-cyan-100',
    };
@endphp

<div {{ $attributes->merge(['class' => $base.' '.$variants]) }}>
    @if (filled($title))
        <div class="text-sm font-semibold">{{ $title }}</div>
    @endif
    <div class="mt-1 text-sm opacity-90">
        {{ $slot }}
    </div>
</div>
