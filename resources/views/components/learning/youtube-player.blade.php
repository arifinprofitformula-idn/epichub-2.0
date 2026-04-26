@props([
    'embedUrl',
    'title' => null,
    'description' => null,
])

<div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_24px_rgba(0,0,0,0.07)]">
    {{-- Header --}}
    <div class="border-b border-zinc-200/70 px-4 py-4 md:px-6">
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-100">
                <svg class="h-4 w-4 text-violet-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-zinc-950">Video Materi</div>
                @if ($title)
                    <div class="truncate text-xs text-zinc-500">{{ $title }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Video player --}}
    <div class="bg-zinc-950">
        <div class="aspect-video w-full">
            <iframe
                src="{{ $embedUrl }}"
                class="h-full w-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                title="{{ $title ?? 'Video Materi' }}"
            ></iframe>
        </div>
    </div>

    @if ($description)
        <div class="border-t border-zinc-200/70 px-4 py-4 md:px-6">
            <p class="text-sm leading-relaxed text-zinc-600">{{ $description }}</p>
        </div>
    @endif
</div>
