<?php

namespace App\Filament\Resources\EmailNotificationLogs\Pages;

use App\Filament\Resources\EmailNotificationLogs\EmailNotificationLogResource;
use Filament\Resources\Pages\ListRecords;

class ListEmailNotificationLogs extends ListRecords
{
    protected static string $resource = EmailNotificationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
