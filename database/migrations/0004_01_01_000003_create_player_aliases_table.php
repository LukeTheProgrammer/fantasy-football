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
        Schema::dropIfExists('player_aliases');
        Schema::enableForeignKeyConstraints();

        /**
         * Team and Position IDs are intended to provide the ability
         * to disambiguate players with the same name but different
         * teams or positions.
         */
        Schema::create('player_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['player_id', 'name', 'team_id', 'position_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('player_aliases');
        Schema::enableForeignKeyConstraints();
    }
};
