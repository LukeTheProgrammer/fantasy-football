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
 *
 * This runs hourly rather than at 06:00 alone because FantasyPros keeps no
 * archive of its own: a day the pull does not happen is a day that can never be
 * recovered. A single fixed time is only reached if the machine happens to be up
 * for that one minute, so a reboot spanning it silently loses the day. Running
 * every hour means the first hour the machine is up captures the day instead.
 *
 * The command itself no-ops once the day's run has completed, so the extra
 * invocations cost a marker lookup and nothing more.
 */
Schedule::command('fantasy-pros:daily')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
