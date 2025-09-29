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
        Schema::dropIfExists('teams');
        Schema::enableForeignKeyConstraints();

        Schema::create('teams', function (Blueprint $table) {
            $table->string('id', 10)->primary();
            $table->integer('espn_id')->nullable();
            $table->string('pfr_id')->nullable();
            $table->string('abbreviation');
            $table->string('location');
            $table->string('name');
            $table->string('logo')->nullable();
            $table->enum('conference', ['FA', 'NFC', 'AFC']);
            $table->enum('division', [
                'FA',
                'AFC East',
                'AFC North',
                'AFC South',
                'AFC West',
                'NFC East',
                'NFC North',
                'NFC South',
                'NFC West',
            ]);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('espn_id');
            $table->unique('pfr_id');
            $table->unique('abbreviation');
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('teams');
        Schema::enableForeignKeyConstraints();
    }
};
