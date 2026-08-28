<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a team went into the playoffs and where it came out.
 *
 * Both are the platform's own numbers rather than something derived from the
 * record: the seeding rule is a league setting, so a record alone does not say
 * which team held the bye.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->integer('playoff_seed')->nullable()->after('faab_balance');
            $table->integer('final_rank')->nullable()->after('playoff_seed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->dropColumn(['playoff_seed', 'final_rank']);
        });
    }
};
