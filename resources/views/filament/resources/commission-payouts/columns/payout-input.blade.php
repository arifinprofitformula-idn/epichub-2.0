@php($record = $getRecord())

<div>
    <input
        type="text"
        value="Rp {{ number_format((float) $record->available_commission_total_amount, 0, ',', '.') }}"
        disabled
        maxlength="10"
        class="w-32 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700"
    />
</div>
