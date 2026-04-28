<?php

namespace App\Filament\Resources\LegacyV1ProductMappings\Pages;

use App\Filament\Resources\LegacyV1ProductMappings\LegacyV1ProductMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLegacyV1ProductMappings extends ListRecords
{
    protected static string $resource = LegacyV1ProductMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
