<?php

namespace App\Filament\Resources\LegacyV1Payouts\Pages;

use App\Filament\Resources\LegacyV1Payouts\LegacyV1PayoutResource;
use Filament\Resources\Pages\ListRecords;

class ListLegacyV1Payouts extends ListRecords
{
    protected static string $resource = LegacyV1PayoutResource::class;
}
