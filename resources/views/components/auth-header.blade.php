@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="epi-auth-title text-[2rem] font-extrabold leading-tight md:text-[2.35rem]">{{ $title }}</flux:heading>
    <flux:subheading class="mt-1 text-base font-medium md:text-lg" style="color: var(--body-color);">{{ $description }}</flux:subheading>
</div>
