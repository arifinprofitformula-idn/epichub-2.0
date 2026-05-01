<?php

namespace App\Filament\Resources\EmailNotificationLogs;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\EmailNotificationLogs\Pages\ListEmailNotificationLogs;
use App\Filament\Resources\EmailNotificationLogs\Pages\ViewEmailNotificationLog;
use App\Filament\Resources\EmailNotificationLogs\Tables\EmailNotificationLogsTable;
use App\Models\EmailNotificationLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmailNotificationLogResource extends Resource
{
    protected static ?string $model = EmailNotificationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $navigationLabel = 'Email Logs';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Settings;

    protected static ?int $navigationSort = 85;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return EmailNotificationLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailNotificationLogs::route('/'),
            'view'  => ViewEmailNotificationLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }
}
