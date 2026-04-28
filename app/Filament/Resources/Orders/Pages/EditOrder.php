<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [];
    }
}

