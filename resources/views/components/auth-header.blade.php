@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <a href="{{ route('home') }}" class="mx-auto mb-2 inline-flex items-center justify-center sm:mb-5">
        <img
            src="{{ asset('epic-hub-auth-logo.png') }}"
            alt="EPIC HUB"
            class="h-14 w-14 object-contain sm:h-[100px] sm:w-[100px]"
        />
    </a>
    <flux:heading size="xl" class="epi-auth-title text-[1.45rem] font-extrabold leading-tight sm:text-[2rem] md:text-[2.35rem]">{{ $title }}</flux:heading>
    <flux:subheading class="mt-0.5 text-xs font-medium sm:mt-1 sm:text-base md:text-lg" style="color: var(--body-color);">{{ $description }}</flux:subheading>
</div>
