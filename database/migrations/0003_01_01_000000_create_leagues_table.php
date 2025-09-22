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
        Schema::dropIfExists('leagues');
        Schema::enableForeignKeyConstraints();

        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->integer('year');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('platform')->default('ESPN');
            $table->string('platform_id')->nullable();
            $table->integer('team_count')->default(12);
            $table->boolean('is_public')->default(true);
            $table->string('join_code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->json('credentials')->nullable();
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
        Schema::dropIfExists('leagues');
        Schema::enableForeignKeyConstraints();
    }
};
