<?php

namespace App\Filament\Resources\EmailNotificationLogs\Pages;

use App\Filament\Resources\EmailNotificationLogs\EmailNotificationLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailNotificationLog extends ViewRecord
{
    protected static string $resource = EmailNotificationLogResource::class;

    protected string $view = 'filament.resources.email-notification-logs.pages.view-email-notification-log';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
