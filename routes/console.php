<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Hostinger / shared hosting: add ONE cron job that runs every minute:
|
|   * * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1
|
| Laravel will then execute only the tasks that are due.
|
*/

// Example: prune expired password reset tokens daily (built-in)
Schedule::command('auth:clear-resets')->daily();

// Optional: clear old sessions table if using database sessions
// Schedule::command('session:gc')->daily();
