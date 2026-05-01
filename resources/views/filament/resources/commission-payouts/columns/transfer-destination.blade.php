@php($record = $getRecord())

<div>
    @if ($record->hasCompletePayoutBankInfo())
        <div class="space-y-0.5 text-sm text-gray-700">
            <div class="font-medium text-gray-900">{{ $record->payout_bank_name }}</div>
            <div class="flex items-center gap-1.5" x-data="{ copied: false }">
                <span>{{ $record->payout_bank_account_number }}</span>
                <button
                    type="button"
                    title="Salin nomor rekening"
                    x-on:click="
                        navigator.clipboard.writeText('{{ $record->payout_bank_account_number }}').then(() => {
                            copied = true;
                            setTimeout(() => { copied = false }, 1500);
                        });
                    "
                    class="cursor-pointer transition-colors"
                    :class="copied ? 'text-emerald-500' : 'text-gray-400 hover:text-gray-600'"
                >
                    <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                        <path d="M7 3.5A1.5 1.5 0 0 1 8.5 2h3.879a1.5 1.5 0 0 1 1.06.44l3.122 3.12A1.5 1.5 0 0 1 17 6.622V12.5a1.5 1.5 0 0 1-1.5 1.5h-1v-3.379a3 3 0 0 0-.879-2.121L10.5 5.379A3 3 0 0 0 8.379 4.5H7v-1Zm-2 0v1H4.5A1.5 1.5 0 0 0 3 6v10.5A1.5 1.5 0 0 0 4.5 18h7A1.5 1.5 0 0 0 13 16.5v-1h1.5A1.5 1.5 0 0 0 16 14V6.622a1.5 1.5 0 0 0-.44-1.06L12.44 2.44A1.5 1.5 0 0 0 11.379 2H8.5A1.5 1.5 0 0 0 7 3.5Z"/>
                    </svg>
                    <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="text-gray-500">{{ $record->payout_bank_account_holder_name }}</div>
        </div>
    @else
        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
            Belum lengkap
        </span>
    @endif
</div>
