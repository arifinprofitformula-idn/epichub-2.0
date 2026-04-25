<?php

namespace App\Enums;

enum EventRegistrationStatus: string
{
    case Registered = 'registered';
    case Attended = 'attended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registered',
            self::Attended => 'Attended',
            self::Cancelled => 'Cancelled',
        };
    }
}

