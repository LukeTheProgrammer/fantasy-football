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

/*
 * ESPN's board is an average over its own leagues, so it moves slowly and a
 * capture a day is plenty. It runs hourly for the same reason the FantasyPros
 * pull does — this machine is not always up at any fixed minute — and the
 * command no-ops once the day is stored, so the extra invocations cost one
 * query each.
 *
 * It is offset ten minutes past the hour so it follows the FantasyPros pull
 * rather than competing with it.
 */
Schedule::command('espn:rankings:import')
    ->hourlyAt(10)
    ->withoutOverlapping()
    ->onOneServer();
