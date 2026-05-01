<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('mailketing:retry-failed --limit=50')->hourly();
Schedule::command('notifications:send-payment-reminders')->hourly();
Schedule::command('notifications:send-event-reminders')->everyFifteenMinutes();
