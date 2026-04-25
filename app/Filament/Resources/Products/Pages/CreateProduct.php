<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Catalog\ExtractProductLandingPageZipAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        if (filled($this->record->landing_page_zip_path)) {
            app(ExtractProductLandingPageZipAction::class)->execute(
                product: $this->record,
                zipPath: (string) $this->record->landing_page_zip_path,
                incrementVersion: false,
            );
        }
    }
}
