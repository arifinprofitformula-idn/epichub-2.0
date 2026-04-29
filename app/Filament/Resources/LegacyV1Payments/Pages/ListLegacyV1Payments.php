<?php

namespace App\Filament\Resources\LegacyV1Payments\Pages;

use App\Filament\Resources\LegacyV1Payments\LegacyV1PaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListLegacyV1Payments extends ListRecords
{
    protected static string $resource = LegacyV1PaymentResource::class;
}
