<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the identifiers that let a player and a game be recognised across
 * sources.
 *
 * nflverse keys players by their NFL gsis id and publishes the pfr and espn id
 * beside it, which is what turns three separately imported player lists into
 * one. Games carry the same treatment, so a stat line can find the game row it
 * belongs to whichever source loaded that row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('gsis_id')->nullable()->unique()->after('fp_id');
        });

        Schema::table('nfl_games', function (Blueprint $table) {
            $table->string('nflverse_id')->nullable()->unique()->after('espn_id');
            $table->string('pfr_id')->nullable()->unique()->after('nflverse_id');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique(['gsis_id']);
            $table->dropColumn('gsis_id');
        });

        Schema::table('nfl_games', function (Blueprint $table) {
            $table->dropUnique(['nflverse_id']);
            $table->dropUnique(['pfr_id']);
            $table->dropColumn(['nflverse_id', 'pfr_id']);
        });
    }
};
