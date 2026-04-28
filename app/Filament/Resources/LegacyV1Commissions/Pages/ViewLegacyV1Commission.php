<?php

namespace App\Filament\Resources\LegacyV1Commissions\Pages;

use App\Filament\Resources\LegacyV1Commissions\LegacyV1CommissionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLegacyV1Commission extends ViewRecord
{
    protected static string $resource = LegacyV1CommissionResource::class;

    protected string $view = 'filament.resources.legacy-v1-commissions.pages.view-legacy-v1-commission';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing(['importBatch', 'user.epiChannel', 'epiChannel', 'product', 'payout', 'errors.resolvedBy']);
    }
}
