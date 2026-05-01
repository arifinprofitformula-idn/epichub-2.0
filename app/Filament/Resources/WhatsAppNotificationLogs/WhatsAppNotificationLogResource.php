<?php

namespace App\Filament\Resources\WhatsAppNotificationLogs;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\WhatsAppNotificationLogs\Pages\ListWhatsAppNotificationLogs;
use App\Filament\Resources\WhatsAppNotificationLogs\Pages\ViewWhatsAppNotificationLog;
use App\Filament\Resources\WhatsAppNotificationLogs\Tables\WhatsAppNotificationLogsTable;
use App\Models\WhatsAppNotificationLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WhatsAppNotificationLogResource extends Resource
{
    protected static ?string $model = WhatsAppNotificationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static ?string $navigationLabel = 'WhatsApp Logs';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Settings;

    protected static ?int $navigationSort = 86;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppNotificationLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsAppNotificationLogs::route('/'),
            'view' => ViewWhatsAppNotificationLog::route('/{record}'),
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
