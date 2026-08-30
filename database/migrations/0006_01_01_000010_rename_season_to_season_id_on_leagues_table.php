<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames leagues.season to leagues.season_id.
 *
 * The column is a key into seasons, and the model wants to say so with a
 * belongsTo. It cannot while the column is called season: Eloquent resolves
 * the attribute before the relation, so $league->season would keep returning
 * the year and $league->season() the query. The name follows the key.
 *
 * The foreign key is dropped and rebuilt around the rename -- MySQL will not
 * rename a column an index still points at by its old name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropForeign(['season']);
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->renameColumn('season', 'season_id');
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->foreign('season_id')->references('id')->on('seasons')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropForeign(['season_id']);
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->renameColumn('season_id', 'season');
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->foreign('season')->references('id')->on('seasons')->cascadeOnUpdate();
        });
    }
};
