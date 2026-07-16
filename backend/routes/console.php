<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Queue birthday-wish SMS once a day. Fires via `schedule:run`; on cPanel the
// command can equally be cronned directly — its cache guard makes double runs
// harmless either way.
Schedule::command('sms:birthday-wishes')->dailyAt('00:00')->timezone('Asia/Dhaka');
