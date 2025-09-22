<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerProjection extends Model
{
    use HasFactory;

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
        'season'                => 'integer',
        'week'                  => 'integer',
        'fantasy_points'        => 'decimal:2',
        'espn_projected_points' => 'decimal:2',
        'fp_projected_points'   => 'decimal:2',
        'fp_position_rank'      => 'integer',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Relationship to Player.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Relationship to NFL Game.
     */
    public function nflGame(): BelongsTo
    {
        return $this->belongsTo(NFLGame::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope a query by player_id.
     *
     * @param Builder $query
     * @param integer|string|Player $player
     *
     * @return Builder
     */
    public function scopeForPlayer(Builder $query, int|string|Player $player): Builder
    {
        return $query->where('player_id', ($player instanceof Player) ? $player->id : $player);
    }

    /**
     * Scope a query by nfl_game_id.
     *
     * @param Builder $query
     * @param integer|string|NFLGame $game
     *
     * @return Builder
     */
    public function scopeForNFLGame(Builder $query, int|string|NFLGame $game): Builder
    {
        return $query->where('nfl_game_id', ($game instanceof NFLGame) ? $game->id : $game);
    }

}
