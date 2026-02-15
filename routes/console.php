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

// Daily funding scrape - runs at 2am
Schedule::command('schedule:funding')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/schedule-funding.log'));

// Daily company discovery - staggered by source to spread load
Schedule::command('companies:discover --source=techcrunch')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

Schedule::command('companies:discover --source=yc')
    ->dailyAt('04:15')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

Schedule::command('companies:discover --source=crunchbase')
    ->dailyAt('04:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

Schedule::command('companies:discover --source=wellfound')
    ->dailyAt('04:45')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

Schedule::command('companies:discover --source=hackernews')
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

// Nightly OSS project discovery - runs at 5:30am
Schedule::command('app:discover-oss-projects')
    ->dailyAt('05:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/oss-discover.log'));

// Weekly OSS star refresh - runs Sundays at 6:30am
Schedule::command('app:refresh-oss-stars')
    ->weeklyOn(0, '06:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/oss-refresh-stars.log'));

Schedule::command('companies:discover --source=producthunt')
    ->dailyAt('05:15')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/companies-discover.log'));

// Daily news fetch - runs at 3am
Schedule::command('news:fetch')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/news-fetch.log'));
