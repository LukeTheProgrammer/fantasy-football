<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Superflex is a scoring format of its own: the same player carries a different
 * rank once quarterbacks can fill a flex spot. Without this column a superflex
 * board would collide with the standard board it shares a ppr value with.
 *
 * The unique index cannot be replaced while it is the only index backing the
 * player_id foreign key, so each table gets a plain index on that column first.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('draft_rankings', function (Blueprint $table) {
            $table->boolean('superflex')->default(false)->after('ppr');
            $table->index('player_id', 'draft_rankings_player_index');
        });

        Schema::table('draft_rankings', function (Blueprint $table) {
            $table->dropUnique('draft_rankings_unique');
            $table->unique(
                ['player_id', 'season', 'ranked_at', 'type', 'source', 'ppr', 'superflex'],
                'draft_rankings_unique'
            );
        });

        Schema::table('player_ranking_averages', function (Blueprint $table) {
            $table->boolean('superflex')->default(false)->after('ppr');
        });

        Schema::table('player_ranking_averages', function (Blueprint $table) {
            $table->dropUnique('player_ranking_averages_unique');
            $table->unique(
                ['player_id', 'season', 'ranked_on', 'type', 'ppr', 'superflex'],
                'player_ranking_averages_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_rankings', function (Blueprint $table) {
            $table->dropUnique('draft_rankings_unique');
            $table->unique(
                ['player_id', 'season', 'ranked_at', 'type', 'source', 'ppr'],
                'draft_rankings_unique'
            );
        });

        Schema::table('draft_rankings', function (Blueprint $table) {
            $table->dropIndex('draft_rankings_player_index');
            $table->dropColumn('superflex');
        });

        Schema::table('player_ranking_averages', function (Blueprint $table) {
            $table->dropUnique('player_ranking_averages_unique');
            $table->unique(
                ['player_id', 'season', 'ranked_on', 'type', 'ppr'],
                'player_ranking_averages_unique'
            );
        });

        Schema::table('player_ranking_averages', function (Blueprint $table) {
            $table->dropColumn('superflex');
        });
    }
};
