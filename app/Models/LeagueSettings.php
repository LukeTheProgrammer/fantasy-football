<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueSettings extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'league_id',
        'roster_positions',
        'roster_size',
        'starters_count',
        'bench_count',
        'ir_spots',
        'passing_yards_per_point',
        'passing_td_points',
        'interception_points',
        'rushing_yards_per_point',
        'rushing_td_points',
        'receiving_yards_per_point',
        'receiving_td_points',
        'reception_points',
        'fumble_lost_points',
        'two_point_conversion_points',
        'field_goal_0_39_points',
        'field_goal_40_49_points',
        'field_goal_50_plus_points',
        'extra_point_points',
        'defense_sack_points',
        'defense_interception_points',
        'defense_fumble_recovery_points',
        'defense_td_points',
        'defense_safety_points',
        'defense_points_allowed_tiers',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'roster_positions' => 'array',
        'defense_points_allowed_tiers' => 'array',
        'passing_yards_per_point' => 'decimal:2',
        'passing_td_points' => 'decimal:2',
        'interception_points' => 'decimal:2',
        'rushing_yards_per_point' => 'decimal:2',
        'rushing_td_points' => 'decimal:2',
        'receiving_yards_per_point' => 'decimal:2',
        'receiving_td_points' => 'decimal:2',
        'reception_points' => 'decimal:2',
        'fumble_lost_points' => 'decimal:2',
        'two_point_conversion_points' => 'decimal:2',
        'field_goal_0_39_points' => 'decimal:2',
        'field_goal_40_49_points' => 'decimal:2',
        'field_goal_50_plus_points' => 'decimal:2',
        'extra_point_points' => 'decimal:2',
        'defense_sack_points' => 'decimal:2',
        'defense_interception_points' => 'decimal:2',
        'defense_fumble_recovery_points' => 'decimal:2',
        'defense_td_points' => 'decimal:2',
        'defense_safety_points' => 'decimal:2',
    ];
    
    /**
     * Get the league that owns the settings.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
}
