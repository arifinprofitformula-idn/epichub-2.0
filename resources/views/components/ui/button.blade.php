@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-[var(--radius-md)] font-semibold transition outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-accent-foreground disabled:pointer-events-none disabled:opacity-60';

    $sizes = match ($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'lg' => 'px-5 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };

    $variants = match ($variant) {
        'secondary' => 'bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-zinc-800 dark:hover:bg-zinc-700',
        'ghost' => 'bg-transparent text-zinc-900 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-800',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600',
        default => 'bg-accent text-accent-foreground hover:brightness-95 dark:hover:brightness-105',
    };

    $classes = $base.' '.$sizes.' '.$variants;
@endphp

@if (filled($href))
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
