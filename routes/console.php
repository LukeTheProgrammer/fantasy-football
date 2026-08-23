<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Rankings move daily, so a capture is taken every morning and imported the
 * same run. The averages snapshot is what turns those captures into a player's
 * value over time, so it runs last, after the day's rankings have landed.
 */
Schedule::command('fantasy-pros:daily')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer();
