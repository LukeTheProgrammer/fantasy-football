<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a matchup is for, so the playoffs can be told from the regular season
 * and the championship bracket from the consolation ladder beside it.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('league_matchups', function (Blueprint $table) {
            // The platform's own name for the bracket this game belongs to, so
            // a consolation game is never mistaken for a playoff one.
            $table->string('playoff_tier')->nullable()->after('week');

            // Which side won, as the platform recorded it: a tie is broken by
            // rules the scores alone do not show.
            $table->string('winner')->nullable()->after('is_complete');
        });

        // A first round bye is a real matchup with one team in it, so the away
        // side has to be allowed to be missing.
        Schema::table('league_matchups', function (Blueprint $table) {
            $table->foreignId('away_member_id')->nullable()->change();
        });

        Schema::table('league_settings', function (Blueprint $table) {
            $table->integer('playoff_team_count')->nullable()->after('ir_spots');
            $table->integer('regular_season_weeks')->nullable()->after('playoff_team_count');
            $table->integer('playoff_matchup_length')->nullable()->after('regular_season_weeks');
            $table->boolean('playoff_reseed')->default(false)->after('playoff_matchup_length');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_matchups', function (Blueprint $table) {
            $table->dropColumn(['playoff_tier', 'winner']);
        });

        Schema::table('league_settings', function (Blueprint $table) {
            $table->dropColumn(['playoff_team_count', 'regular_season_weeks', 'playoff_matchup_length', 'playoff_reseed']);
        });
    }
};
