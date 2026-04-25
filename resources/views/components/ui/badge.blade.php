@props([
    'variant' => 'neutral',
])

@php
    $base = 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold';

    $variants = match ($variant) {
        'success' => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
        'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
        'danger' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
        'info' => 'bg-cyan-100 text-cyan-900 dark:bg-cyan-950 dark:text-cyan-200',
        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200',
    };
@endphp

<span {{ $attributes->merge(['class' => $base.' '.$variants]) }}>
    {{ $slot }}
</span>
