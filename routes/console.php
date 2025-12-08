<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendEventRemindersJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Schedule event reminders
// Send reminder 24 hours before event
Schedule::job(new SendEventRemindersJob(24))->dailyAt('09:00');

// Send reminder 3 hours before event
Schedule::job(new SendEventRemindersJob(3))->hourly();

// Send reminder 1 hour before event
Schedule::job(new SendEventRemindersJob(1))->hourly();