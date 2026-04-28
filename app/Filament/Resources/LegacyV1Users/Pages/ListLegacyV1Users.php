<?php

namespace App\Filament\Resources\LegacyV1Users\Pages;

use App\Filament\Resources\LegacyV1Users\LegacyV1UserResource;
use Filament\Resources\Pages\ListRecords;

class ListLegacyV1Users extends ListRecords
{
    protected static string $resource = LegacyV1UserResource::class;
}
