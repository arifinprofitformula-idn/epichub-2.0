<?php

namespace App\Filament\Resources\LegacyV1ProductMappings\Pages;

use App\Filament\Resources\LegacyV1ProductMappings\LegacyV1ProductMappingResource;
use Filament\Resources\Pages\EditRecord;

class EditLegacyV1ProductMapping extends EditRecord
{
    protected static string $resource = LegacyV1ProductMappingResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['mapped_by'] = auth()->id();
        $data['mapped_at'] = now();
        $data['legacy_product_key'] = strtolower(trim((string) $data['legacy_product_key']));

        return $data;
    }
}
