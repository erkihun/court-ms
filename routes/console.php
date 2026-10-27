<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// EUISIS master data and referred-case changes (docs/euisis-integration.md).
// Needs `php artisan schedule:run` every minute on the server.
Schedule::command('euisis:sync')->everyFiveMinutes()->withoutOverlapping()->runInBackground();
