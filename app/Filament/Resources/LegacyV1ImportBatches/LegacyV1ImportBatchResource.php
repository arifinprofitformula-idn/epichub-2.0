<?php

namespace App\Filament\Resources\LegacyV1ImportBatches;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1ImportBatches\Pages\ListLegacyV1ImportBatches;
use App\Filament\Resources\LegacyV1ImportBatches\Tables\LegacyV1ImportBatchesTable;
use App\Models\LegacyV1ImportBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1ImportBatchResource extends Resource
{
    protected static ?string $model = LegacyV1ImportBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Legacy Import Batches';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 50;

    public static function table(Table $table): Table
    {
        return LegacyV1ImportBatchesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1ImportBatches::route('/'),
        ];
    }
}
