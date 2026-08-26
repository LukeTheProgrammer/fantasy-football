<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives a player's team history a season.
 *
 * Without one the table can only say where a player is now, so importing a past
 * season's rosters overwrites the present. With one, a player who was traded
 * simply has two rows for that season, and the week by week truth lives in his
 * stat lines.
 *
 * Existing rows are stamped with the current season, which is what they have
 * always meant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_teams', function (Blueprint $table) {
            $table->year('season')->nullable()->after('team_id');
            $table->string('source')->nullable()->after('season');
        });

        $current = DB::table('seasons')->where('is_current', true)->value('id')
            ?? DB::table('seasons')->max('id');

        if ($current) {
            DB::table('player_teams')->whereNull('season')->update(['season' => $current]);
        }

        Schema::table('player_teams', function (Blueprint $table) {
            $table->unique(['player_id', 'team_id', 'season'], 'player_teams_unique');
            $table->index(['season', 'team_id'], 'player_teams_season_team_index');
        });
    }

    public function down(): void
    {
        Schema::table('player_teams', function (Blueprint $table) {
            $table->dropUnique('player_teams_unique');
            $table->dropIndex('player_teams_season_team_index');
            $table->dropColumn(['season', 'source']);
        });
    }
};
