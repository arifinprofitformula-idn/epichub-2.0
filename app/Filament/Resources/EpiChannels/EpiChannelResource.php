<?php

namespace App\Filament\Resources\EpiChannels;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\EpiChannels\Pages\CreateEpiChannel;
use App\Filament\Resources\EpiChannels\Pages\EditEpiChannel;
use App\Filament\Resources\EpiChannels\Pages\ListEpiChannels;
use App\Filament\Resources\EpiChannels\Schemas\EpiChannelForm;
use App\Filament\Resources\EpiChannels\Tables\EpiChannelsTable;
use App\Models\EpiChannel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EpiChannelResource extends Resource
{
    protected static ?string $model = EpiChannel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $recordTitleAttribute = 'epic_code';

    protected static ?string $navigationLabel = 'Channel EPI';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Afiliasi;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return EpiChannelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EpiChannelsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEpiChannels::route('/'),
            'create' => CreateEpiChannel::route('/create'),
            'edit' => EditEpiChannel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
