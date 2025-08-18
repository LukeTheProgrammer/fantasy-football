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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->integer('espn_id')->nullable();
            $table->string('abbreviation');
            $table->string('location');
            $table->string('name');
            $table->string('logo')->nullable();
            $table->enum('conference', ['NFC', 'AFC']);
            $table->enum('division', [
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
