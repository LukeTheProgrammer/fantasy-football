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
        Schema::create('league_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            
            // Roster settings
            $table->json('roster_positions'); // JSON array of required positions
            $table->integer('roster_size')->default(16);
            $table->integer('starters_count')->default(9);
            $table->integer('bench_count')->default(7);
            $table->integer('ir_spots')->default(1);
            
            // Scoring settings
            $table->decimal('passing_yards_per_point', 8, 2)->default(0.04); // 1 point per 25 yards
            $table->decimal('passing_td_points', 8, 2)->default(4.0);
            $table->decimal('interception_points', 8, 2)->default(-2.0);
            $table->decimal('rushing_yards_per_point', 8, 2)->default(0.1); // 1 point per 10 yards
            $table->decimal('rushing_td_points', 8, 2)->default(6.0);
            $table->decimal('receiving_yards_per_point', 8, 2)->default(0.1); // 1 point per 10 yards
            $table->decimal('receiving_td_points', 8, 2)->default(6.0);
            $table->decimal('reception_points', 8, 2)->default(0.0); // PPR setting
            $table->decimal('fumble_lost_points', 8, 2)->default(-2.0);
            $table->decimal('two_point_conversion_points', 8, 2)->default(2.0);
            
            // Kicking settings
            $table->decimal('field_goal_0_39_points', 8, 2)->default(3.0);
            $table->decimal('field_goal_40_49_points', 8, 2)->default(4.0);
            $table->decimal('field_goal_50_plus_points', 8, 2)->default(5.0);
            $table->decimal('extra_point_points', 8, 2)->default(1.0);
            
            // Defense settings
            $table->decimal('defense_sack_points', 8, 2)->default(1.0);
            $table->decimal('defense_interception_points', 8, 2)->default(2.0);
            $table->decimal('defense_fumble_recovery_points', 8, 2)->default(2.0);
            $table->decimal('defense_td_points', 8, 2)->default(6.0);
            $table->decimal('defense_safety_points', 8, 2)->default(2.0);
            $table->json('defense_points_allowed_tiers')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_settings');
    }
};
