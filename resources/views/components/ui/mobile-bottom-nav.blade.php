@php
    $items = [
        [
            'label' => 'Ringkasan',
            'href' => '#top',
            'icon' => '<path d="M10.25 3.75h3.5c2.485 0 4.5 2.015 4.5 4.5v7.5c0 2.485-2.015 4.5-4.5 4.5h-3.5c-2.485 0-4.5-2.015-4.5-4.5v-7.5c0-2.485 2.015-4.5 4.5-4.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.25 12h7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        ],
        [
            'label' => 'Produk Saya',
            'href' => '#produk-saya',
            'icon' => '<path d="M4.75 7.75h14.5M6.75 7.75V6.5c0-1.519 1.231-2.75 2.75-2.75h5c1.519 0 2.75 1.231 2.75 2.75v1.25M7.25 7.75l.9 12h7.7l.9-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        ],
        [
            'label' => 'Kelas',
            'href' => '#kelas',
            'icon' => '<path d="M4.75 6.75c0-.552.448-1 1-1h12.5c.552 0 1 .448 1 1v10.5c0 .552-.448 1-1 1H5.75c-.552 0-1-.448-1-1V6.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8 9h8M8 12h8M8 15h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        ],
        [
            'label' => 'Event',
            'href' => '#event',
            'icon' => '<path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        ],
        [
            'label' => 'Profil',
            'href' => route('profile.edit'),
            'icon' => '<path d="M12 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M4.75 20.25c1.6-3.1 4.3-4.5 7.25-4.5s5.65 1.4 7.25 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        ],
    ];
@endphp

<nav class="md:hidden fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200/70 bg-white/80 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/70">
    <div class="mx-auto flex max-w-[var(--container-5xl)] items-center justify-between px-3 py-2">
        @foreach ($items as $item)
            <a href="{{ $item['href'] }}" class="flex w-full flex-col items-center justify-center gap-1 rounded-[var(--radius-md)] px-2 py-2 text-xs font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg">
                    {!! $item['icon'] !!}
                </svg>
                <span class="leading-none">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
