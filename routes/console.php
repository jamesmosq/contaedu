<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:expire-institution-contracts')->dailyAt('00:05');
Schedule::command('app:close-abandoned-sessions')->hourly();
Schedule::command('telescope:prune --hours=72')->dailyAt('01:00');
