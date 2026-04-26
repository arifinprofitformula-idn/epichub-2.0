@php
    $statusVal = $status?->value ?? $status ?? '';
    $label = $status?->label() ?? ucfirst((string) $statusVal);

    $config = match($statusVal) {
        'approved' => [
            'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
            'icon' => '<path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        ],
        'paid' => [
            'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            'icon' => '<rect x="3.75" y="6.75" width="16.5" height="11.5" rx="2.25" stroke="currentColor" stroke-width="1.5"/><path d="M3.75 10H20.25" stroke="currentColor" stroke-width="1.5"/><circle cx="8.5" cy="14" r="1" fill="currentColor"/>',
        ],
        'rejected' => [
            'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
            'icon' => '<path d="M9.5 9.5L14.5 14.5M14.5 9.5L9.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        ],
        default => [
            'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            'icon' => '<circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/><path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        ],
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $config['class'] }}">
    <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        {!! $config['icon'] !!}
    </svg>
    {{ $label }}
</span>
