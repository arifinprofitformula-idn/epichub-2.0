<?php

namespace App\Filament\Resources\EpiChannels\Pages;

use App\Filament\Resources\EpiChannels\EpiChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditEpiChannel extends EditRecord
{
    protected static string $resource = EpiChannelResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

