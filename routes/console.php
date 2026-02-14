<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| These tasks run automatically when the scheduler is invoked via cron.
| Set up the cron entry: * * * * * cd /path-to-project && php artisan schedule:run
|
*/

// Daily headcount fetching - runs at 6am, distributes companies across 25 days
Schedule::command('schedule:headcounts')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/schedule-headcounts.log'));

// Weekly funding scrape - runs Sunday at 2am
Schedule::command('schedule:funding')
    ->weeklyOn(0, '02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/schedule-funding.log'));

// Weekly company discovery - runs Saturday at 4am
Schedule::command('companies:discover')
    ->weeklyOn(6, '04:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

// Weekly news fetch - runs Monday at 3am
Schedule::command('news:fetch')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/news-fetch.log'));
