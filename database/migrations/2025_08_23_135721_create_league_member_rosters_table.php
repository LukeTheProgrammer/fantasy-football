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
        Schema::create('league_member_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_member_id')->constrained('league_members')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->dateTime('added_at')->default(now());
            $table->dateTime('dropped_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['league_member_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_member_rosters');
    }
};
