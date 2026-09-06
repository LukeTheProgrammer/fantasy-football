<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a slot in the order be used up without a player against it.
 *
 * A draft ends when a roster is full, not when the order runs out, and this
 * league's traded picks leave some teams holding more slots than they have
 * room for. Those slots have to be passed rather than filled, and a pick row
 * with no player is what a passed slot is: the clock reads the order for the
 * first slot nothing sits in, so the pass has to sit there.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('draft_picks', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
        });

        Schema::table('draft_picks', function (Blueprint $table) {
            $table->foreignId('player_id')->nullable()->change();
        });

        Schema::table('draft_picks', function (Blueprint $table) {
            $table->foreign('player_id')->references('id')->on('players')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // A passed slot cannot be described by the old shape, so it goes
        // rather than blocking the column from being made required again.
        DB::table('draft_picks')->whereNull('player_id')->delete();

        Schema::table('draft_picks', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
        });

        Schema::table('draft_picks', function (Blueprint $table) {
            $table->foreignId('player_id')->nullable(false)->change();
        });

        Schema::table('draft_picks', function (Blueprint $table) {
            $table->foreign('player_id')->references('id')->on('players')->cascadeOnDelete();
        });
    }
};
