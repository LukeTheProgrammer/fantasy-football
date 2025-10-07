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
        Schema::dropIfExists('players_not_found');
        Schema::enableForeignKeyConstraints();

        Schema::create('players_not_found', function (Blueprint $table) {
            $table->id();
            $table->string('source_class')->nullable();
            $table->json('source_data');
            $table->string('unique_id_key')->nullable();
            $table->string('unique_id_value')->nullable();
            $table->string('name')->nullable();
            $table->string('position_id')->nullable();
            $table->string('team_id')->nullable();
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
        Schema::dropIfExists('players_not_found');
        Schema::enableForeignKeyConstraints();
    }
};
