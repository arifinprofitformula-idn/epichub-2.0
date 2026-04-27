<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
    case Ongoing = 'ongoing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Closed => 'Closed',
            self::Ongoing => 'Ongoing',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Published, self::Completed => 'success',
            self::Ongoing => 'info',
            self::Draft => 'warning',
            self::Closed => 'gray',
        };
    }
}

