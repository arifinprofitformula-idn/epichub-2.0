<?php

namespace App\Filament\Resources\EpiChannels\Pages;

use App\Filament\Resources\EpiChannels\EpiChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEpiChannel extends EditRecord
{
    protected static string $resource = EpiChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

