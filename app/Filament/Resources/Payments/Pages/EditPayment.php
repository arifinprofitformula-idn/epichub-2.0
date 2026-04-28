<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [];
    }
}

