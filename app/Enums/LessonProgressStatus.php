<?php

namespace App\Enums;

enum LessonProgressStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'Sedang dipelajari',
            self::Completed => 'Selesai',
        };
    }
}

