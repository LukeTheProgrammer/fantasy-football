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
            $table->string('player_ulid');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['player_ulid', 'name']);
            $table->foreign('player_ulid')->references('ulid')->on('players')->cascadeOnDelete();
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
