@php($record = $getRecord())

<div class="space-y-1">
    <div class="font-semibold text-gray-900">
        {{ $record->user?->name ?? $record->store_name ?? 'Member tanpa nama' }}
    </div>
    <div class="text-sm text-gray-600">
        {{ $record->epic_code }}
    </div>
    <div class="text-sm text-gray-600">
        {{ $record->user?->whatsapp_number ?: 'WhatsApp belum diisi' }}
    </div>
    <div class="text-xs text-gray-500">
        {{ $record->user?->email ?: '-' }}
    </div>
</div>
