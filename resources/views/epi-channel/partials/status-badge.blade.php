@php
    $statusValue = $status?->value ?? $status ?? 'inactive';
    $variant = match ($statusValue) {
        'active' => 'success',
        'suspended' => 'danger',
        'qualified', 'prospect' => 'info',
        default => 'warning',
    };
    $label = $status?->label() ?? ucfirst((string) $statusValue);
@endphp

<x-ui.badge :variant="$variant">
    {{ $label }}
</x-ui.badge>
