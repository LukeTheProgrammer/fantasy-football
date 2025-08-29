<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->foreignId('league_season_id')
                ->after('user_id')
                ->nullable()
                ->constrained('league_seasons')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->dropForeign(['league_season_id']);
            $table->dropColumn('league_season_id');
        });
    }
};
