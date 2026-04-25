<?php

namespace App\Filament\Resources\EpiChannels\Pages;

use App\Filament\Resources\EpiChannels\EpiChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEpiChannels extends ListRecords
{
    protected static string $resource = EpiChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

