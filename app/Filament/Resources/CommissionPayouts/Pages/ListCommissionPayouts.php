<?php

namespace App\Filament\Resources\CommissionPayouts\Pages;

use App\Filament\Resources\CommissionPayouts\CommissionPayoutResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissionPayouts extends ListRecords
{
    protected static string $resource = CommissionPayoutResource::class;

    protected ?string $subheading = 'Proses pencairan dana pendapatan untuk Affiliate dan Author.';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
