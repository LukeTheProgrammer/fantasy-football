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
        Schema::dropIfExists('league_matchups');
        Schema::enableForeignKeyConstraints();

        Schema::create('league_matchups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_member_id')->constrained('league_members')->cascadeOnDelete();
            $table->foreignId('away_member_id')->constrained('league_members')->cascadeOnDelete();
            $table->year('season');
            $table->integer('week')->unsigned();
            $table->boolean('is_complete')->default(false);
            $table->decimal('home_score', 8, 2)->nullable();
            $table->decimal('away_score', 8, 2)->nullable();
            $table->decimal('home_projected_score', 8, 2)->nullable();
            $table->decimal('away_projected_score', 8, 2)->nullable();
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
        Schema::dropIfExists('league_matchups');
        Schema::enableForeignKeyConstraints();
    }
};
