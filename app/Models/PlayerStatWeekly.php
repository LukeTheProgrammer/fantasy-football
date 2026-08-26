<?php

namespace App\Models;

use App\Enums\SeasonType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * What one player did in one game.
 */
class PlayerStatWeekly extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'player_stats_weekly';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'season'                  => 'integer',
        'week'                    => 'integer',
        'season_type'             => SeasonType::class,
        'passing_epa'             => 'decimal:3',
        'rushing_epa'             => 'decimal:3',
        'receiving_epa'           => 'decimal:3',
        'target_share'            => 'decimal:4',
        'air_yards_share'         => 'decimal:4',
        'wopr'                    => 'decimal:4',
        'offense_snap_percentage' => 'decimal:4',
        'fantasy_points'          => 'decimal:2',
        'fantasy_points_ppr'      => 'decimal:2',
    ];

    /* ===[ Relationships ]=== */

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function nflGame(): BelongsTo
    {
        return $this->belongsTo(NflGame::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'opponent_team_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /* ===[ Scopes ]=== */

    public function scopeForSeason(Builder $query, int $season): Builder
    {
        return $query->where('season', $season);
    }

    public function scopeForWeek(Builder $query, int $week): Builder
    {
        return $query->where('week', $week);
    }

    public function scopeForPlayer(Builder $query, int $playerId): Builder
    {
        return $query->where('player_id', $playerId);
    }

    public function scopeForTeam(Builder $query, string $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeRegularSeason(Builder $query): Builder
    {
        return $query->where('season_type', SeasonType::REGULAR);
    }
}
