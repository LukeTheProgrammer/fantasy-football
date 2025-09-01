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
        // Schema::create('league_member_rosters', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('league_member_id')->constrained()->onDelete('cascade');
        //     $table->timestamps();
        //     $table->softDeletes();

        //     // Ensure a user can only join a league once
        //     $table->unique(['league_id', 'user_id']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('league_member_rosters');
    }
};
