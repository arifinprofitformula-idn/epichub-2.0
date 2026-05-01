<?php

namespace App\Filament\Resources\WhatsAppNotificationLogs\Pages;

use App\Filament\Resources\WhatsAppNotificationLogs\WhatsAppNotificationLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewWhatsAppNotificationLog extends ViewRecord
{
    protected static string $resource = WhatsAppNotificationLogResource::class;

    protected string $view = 'filament.resources.whatsapp-notification-logs.pages.view-whatsapp-notification-log';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
