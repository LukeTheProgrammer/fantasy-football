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
        Schema::dropIfExists('nfl_games');
        Schema::enableForeignKeyConstraints();

        Schema::create('nfl_games', function (Blueprint $table) {
            $table->id();
            $table->string('espn_id')->nullable();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->year('year');
            $table->integer('week');
            $table->dateTime('start_time')->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_playoff')->default(false);
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
        Schema::dropIfExists('nfl_games');
        Schema::enableForeignKeyConstraints();
    }
};
