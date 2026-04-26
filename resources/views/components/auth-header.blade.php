@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <a href="{{ route('home') }}" class="mx-auto mb-5 inline-flex items-center justify-center">
        <img
            src="{{ asset('epic-hub-auth-logo.png') }}"
            alt="EPIC HUB"
            class="h-[100px] w-[100px] object-contain"
        />
    </a>
    <flux:heading size="xl" class="epi-auth-title text-[2rem] font-extrabold leading-tight md:text-[2.35rem]">{{ $title }}</flux:heading>
    <flux:subheading class="mt-1 text-base font-medium md:text-lg" style="color: var(--body-color);">{{ $description }}</flux:subheading>
</div>
