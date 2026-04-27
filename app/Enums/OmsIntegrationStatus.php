<?php

namespace App\Enums;

enum OmsIntegrationStatus: string
{
    case Success = 'success';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed => 'Failed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Success => 'success',
            self::Failed => 'danger',
        };
    }
}

