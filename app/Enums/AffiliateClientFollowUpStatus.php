<?php

namespace App\Enums;

enum AffiliateClientFollowUpStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Interested = 'interested';
    case NotInterested = 'not_interested';
    case NeedFollowUp = 'need_follow_up';
    case Converted = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baru',
            self::Contacted => 'Sudah Dihubungi',
            self::Interested => 'Tertarik',
            self::NotInterested => 'Tidak Tertarik',
            self::NeedFollowUp => 'Perlu Follow Up',
            self::Converted => 'Converted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'neutral',
            self::Contacted => 'info',
            self::Interested => 'success',
            self::NotInterested => 'danger',
            self::NeedFollowUp => 'warning',
            self::Converted => 'success',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
