@php($record = $getRecord())

<div class="space-y-2">
    @if ($record->hasCompletePayoutBankInfo())
        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
            Rekening Lengkap
        </span>

        <div class="space-y-1 text-sm text-gray-700">
            <div class="font-medium text-gray-900">{{ $record->payout_bank_name }}</div>
            <div>{{ $record->payout_bank_account_number }}</div>
            <div>{{ $record->payout_bank_account_holder_name }}</div>
        </div>
    @else
        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
            Belum lengkap
        </span>
    @endif
</div>
