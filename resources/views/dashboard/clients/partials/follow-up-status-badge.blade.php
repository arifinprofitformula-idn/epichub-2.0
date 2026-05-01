@php
    $followUpStatus = $followUpStatus ?? null;
    $label = $followUpStatus?->label() ?? 'Belum ada';
    $variant = $followUpStatus?->color() ?? 'neutral';
@endphp

<x-ui.badge :variant="$variant">
    {{ $label }}
</x-ui.badge>
