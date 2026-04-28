<?php

namespace App\Filament\Resources\EpiChannels\Pages;

use App\Filament\Resources\EpiChannels\EpiChannelResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateEpiChannel extends CreateRecord
{
    protected static string $resource = EpiChannelResource::class;

    protected Width|string|null $maxContentWidth = 'full';
}

