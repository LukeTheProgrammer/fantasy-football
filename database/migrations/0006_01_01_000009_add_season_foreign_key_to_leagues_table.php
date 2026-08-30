<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Points leagues.season at the seasons table.
 *
 * The column already holds the year, and seasons keys itself by the year
 * rather than by a surrogate id, so this is a constraint over the values that
 * are there rather than a new shape: no data moves and no code that reads
 * $league->season changes.
 *
 * The column is widened to unsigned first because MySQL will not point a
 * signed int at an unsigned key. Any season a league claims but the seasons
 * table is missing is created before the key goes on, so an import that ran
 * ahead of the season list cannot block the migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        $missing = DB::table('leagues')
            ->select('season')
            ->distinct()
            ->whereNotIn('season', DB::table('seasons')->pluck('id'))
            ->pluck('season');

        foreach ($missing as $season) {
            DB::table('seasons')->insert([
                'id'         => $season,
                'is_current' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('leagues', function (Blueprint $table) {
            $table->unsignedInteger('season')->change();
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->foreign('season')->references('id')->on('seasons')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropForeign(['season']);
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->integer('season')->change();
        });
    }
};
