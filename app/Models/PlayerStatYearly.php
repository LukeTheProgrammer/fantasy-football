<?php

namespace App\Models;

use App\Enums\SeasonType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * What one player did across a season.
 */
class PlayerStatYearly extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'player_stats_yearly';

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
        'season'             => 'integer',
        'season_type'        => SeasonType::class,
        'games_played'       => 'integer',
        'passing_epa'        => 'decimal:3',
        'rushing_epa'        => 'decimal:3',
        'receiving_epa'      => 'decimal:3',
        'target_share'       => 'decimal:4',
        'air_yards_share'    => 'decimal:4',
        'wopr'               => 'decimal:4',
        'fantasy_points'     => 'decimal:2',
        'fantasy_points_ppr' => 'decimal:2',
    ];

    /* ===[ Relationships ]=== */

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
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

    public function scopeForPlayer(Builder $query, int $playerId): Builder
    {
        return $query->where('player_id', $playerId);
    }

    public function scopeRegularSeason(Builder $query): Builder
    {
        return $query->where('season_type', SeasonType::REGULAR);
    }
}
