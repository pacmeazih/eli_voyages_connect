<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Workflow Automation Schedules
Schedule::command('documents:send-reminders')->dailyAt('09:00');
Schedule::command('appointments:send-reminders')->hourly();
Schedule::command('dossiers:auto-assign --strategy=workload')->hourly()->between('9:00', '17:00');
Schedule::command('dossiers:archive-old')->monthlyOn(1, '00:00');

// Backup Schedules
Schedule::command('backup:run')->daily()->at('01:00');
Schedule::command('backup:clean')->daily()->at('02:00');
Schedule::command('backup:monitor')->daily()->at('03:00');
