<?php

namespace App\Filament\Resources\LegacyV1Orders\Pages;

use App\Filament\Resources\LegacyV1Orders\LegacyV1OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListLegacyV1Orders extends ListRecords
{
    protected static string $resource = LegacyV1OrderResource::class;
}
