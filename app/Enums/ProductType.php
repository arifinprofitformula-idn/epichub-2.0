<?php

namespace App\Enums;

enum ProductType: string
{
    case Ebook = 'ebook';
    case Course = 'course';
    case Membership = 'membership';
    case Event = 'event';
    case Bundle = 'bundle';
    case DigitalFile = 'digital_file';

    public function label(): string
    {
        return match ($this) {
            self::Ebook => 'Ebook',
            self::Course => 'Ecourse',
            self::Membership => 'Membership',
            self::Event => 'Event',
            self::Bundle => 'Bundle',
            self::DigitalFile => 'Digital file',
        };
    }
}
