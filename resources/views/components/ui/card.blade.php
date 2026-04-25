@props([
    'variant' => 'default',
])

@php
    $base = 'rounded-[var(--radius-2xl)] border border-zinc-200/70 bg-white shadow-[var(--shadow-soft)] dark:border-zinc-800 dark:bg-zinc-900';
    $variants = match ($variant) {
        'surface' => 'bg-zinc-50/70 dark:bg-zinc-900/60',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => trim($base.' '.$variants)]) }}>
    {{ $slot }}
</div>
