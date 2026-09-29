<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:database-s3')
    ->dailyAt(config('backup.schedule', '02:00'))
    ->withoutOverlapping()
    ->runInBackground();
