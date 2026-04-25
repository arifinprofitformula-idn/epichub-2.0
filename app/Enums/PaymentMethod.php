<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ManualBankTransfer = 'manual_bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::ManualBankTransfer => 'Transfer bank (manual)',
        };
    }
}
