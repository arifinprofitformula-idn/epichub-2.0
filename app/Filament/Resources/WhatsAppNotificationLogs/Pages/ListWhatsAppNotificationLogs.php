<?php

namespace App\Filament\Resources\WhatsAppNotificationLogs\Pages;

use App\Filament\Resources\WhatsAppNotificationLogs\WhatsAppNotificationLogResource;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppNotificationLogs extends ListRecords
{
    protected static string $resource = WhatsAppNotificationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
