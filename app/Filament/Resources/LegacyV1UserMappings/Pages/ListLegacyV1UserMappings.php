<?php

namespace App\Filament\Resources\LegacyV1UserMappings\Pages;

use App\Filament\Resources\LegacyV1UserMappings\LegacyV1UserMappingResource;
use Filament\Resources\Pages\ListRecords;

class ListLegacyV1UserMappings extends ListRecords
{
    protected static string $resource = LegacyV1UserMappingResource::class;
}
