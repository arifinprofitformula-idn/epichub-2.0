<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Catalog\ExtractProductLandingPageZipAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected ?string $originalLandingPageZipPath = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        $this->originalLandingPageZipPath = $this->record->landing_page_zip_path;
    }

    protected function afterSave(): void
    {
        if (! filled($this->record->landing_page_zip_path)) {
            $this->record->forceFill([
                'landing_page_extract_path' => null,
                'landing_page_uploaded_at' => null,
            ])->saveQuietly();

            $this->originalLandingPageZipPath = null;

            return;
        }

        $shouldIncrementVersion = filled($this->originalLandingPageZipPath)
            && $this->originalLandingPageZipPath !== $this->record->landing_page_zip_path;

        app(ExtractProductLandingPageZipAction::class)->execute(
            product: $this->record,
            zipPath: (string) $this->record->landing_page_zip_path,
            incrementVersion: $shouldIncrementVersion,
        );

        $this->originalLandingPageZipPath = $this->record->fresh()->landing_page_zip_path;
    }
}
