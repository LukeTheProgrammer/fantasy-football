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
            $table->string('home_team_id', 10);
            $table->string('away_team_id', 10)->nullable();
            $table->year('season');
            $table->integer('week');
            $table->boolean('is_bye')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_playoff')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('home_team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('away_team_id')->references('id')->on('teams')->cascadeOnDelete();
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
