@props([
    'sidebar' => false,
    'href' => '/',
])

@php
    $baseClasses = $sidebar
        ? 'flex h-10 items-center gap-3 px-2 in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:px-0 in-data-flux-sidebar-collapsed-desktop:justify-center'
        : 'flex h-10 items-center me-4 gap-3';
@endphp

<a href="{{ $href }}" {{ $attributes->except(['wire:navigate'])->class($baseClasses) }} @if($attributes->has('wire:navigate')) wire:navigate @endif>
    <span class="flex h-[60px] w-[60px] shrink-0 items-center justify-center rounded-[1rem] bg-white p-1 shadow-sm">
        <img
            src="{{ asset('epic-hub-auth-logo.png') }}"
            alt="EPIC HUB"
            class="h-full w-full object-contain"
        />
    </span>

    <span class="{{ $sidebar ? 'in-data-flux-sidebar-collapsed-desktop:hidden' : '' }} epi-brand-copy">
        <span class="epi-brand-title">EPIC HUB</span>
        <span class="epi-brand-tagline">Connect Grow Impact</span>
    </span>
</a>

<style>
    .epi-brand-copy {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: max-content;
        line-height: 1;
    }

    .epi-brand-title {
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        color: inherit;
    }

    .epi-brand-tagline {
        margin-top: 0.22rem;
        width: 100%;
        font-size: 0.5rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        white-space: nowrap;
        text-align: left;
        transform: scaleX(0.9);
        transform-origin: left center;
        opacity: 0.78;
    }
</style>
