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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('fantasy_points_seasons');
        Schema::enableForeignKeyConstraints();

        Schema::create('fantasy_points_seasons', function (Blueprint $table) {
            $table->id();
            $table->year('year')->default(now()->year);
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->decimal('points', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('fantasy_points_seasons');
        Schema::enableForeignKeyConstraints();
    }
};
