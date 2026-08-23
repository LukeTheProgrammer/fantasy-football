<?php

namespace App\Models;

use App\Enums\Datum;
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
        'season'           => 'integer',
        'week'             => 'integer',
        'ppr'              => 'decimal:2',
        'superflex'        => 'boolean',
        'projected_points' => 'decimal:2',
        'pos_rank'         => 'integer',
        'pos_rank_min'     => 'integer',
        'pos_rank_max'     => 'integer',
        'pos_rank_avg'     => 'decimal:2',
        'pos_rank_std'     => 'decimal:2',
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
     * @param int|string|Player $player
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
     * @param int|string|NFLGame $game
     *
     * @return Builder
     */
    public function scopeForNFLGame(Builder $query, int|string|NFLGame $game): Builder
    {
        return $query->where('nfl_game_id', ($game instanceof NFLGame) ? $game->id : $game);
    }

    public function scopeForSeason(Builder $query, int $season): Builder
    {
        return $query->where('season', $season);
    }

    public function scopeForWeek(Builder $query, int $week): Builder
    {
        return $query->where('week', $week);
    }

    /**
     * Scope a query to projections made by one source.
     */
    public function scopeFromSource(Builder $query, string|Datum $source): Builder
    {
        return $query->where('source', $source instanceof Datum ? $source->value : $source);
    }

    /**
     * Scope a query to a single scoring format.
     */
    public function scopeForFormat(Builder $query, float $ppr = 0, bool $superflex = false): Builder
    {
        return $query->where('ppr', $ppr)->where('superflex', $superflex);
    }
}
