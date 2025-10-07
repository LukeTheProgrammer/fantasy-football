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
        Schema::dropIfExists('weeks');
        Schema::enableForeignKeyConstraints();

        Schema::create('weeks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('season_id');
            $table->unsignedInteger('week');
            $table->boolean('is_current')->default(false);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('season_id')->references('id')->on('seasons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('weeks');
        Schema::enableForeignKeyConstraints();
    }
};
