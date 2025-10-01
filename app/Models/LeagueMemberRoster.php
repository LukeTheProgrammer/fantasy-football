<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeagueMemberRoster extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'position_rank'         => 'integer',
        'overall_rank'          => 'integer',
        'fantasy_points'        => 'decimal:2',
        'espn_projected_points' => 'decimal:2',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Get the league that the member belongs to.
     */
    public function leagueMember(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class);
    }

    /**
     * Get the user that the member represents.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the NFL game that the member represents.
     */
    public function nflGame(): BelongsTo
    {
        return $this->belongsTo(NflGame::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope a query to only include rosters for the given league member.
     */
    public function scopeForLeagueMember(Builder $query, int|string|LeagueMember $leagueMember): Builder
    {
        return $query->where('league_member_id', ($leagueMember instanceof LeagueMember) ? $leagueMember->id : $leagueMember);
    }

    /**
     * Scope a query to only include rosters for the given NFL game.
     */
    public function scopeForNflGame(Builder $query, int|string|NflGame $nflGame): Builder
    {
        return $query->where('nfl_game_id', ($nflGame instanceof NflGame) ? $nflGame->id : $nflGame);
    }

    /**
     * Scope a query to only include rosters for the given player.
     */
    public function scopeForPlayer(Builder $query, int|string|Player $player): Builder
    {
        return $query->where('player_id', ($player instanceof Player) ? $player->id : $player);
    }

    /**
     * Scope a query to only include rosters for the given season.
     */
    public function scopeForSeason(Builder $query, int|string $season): Builder
    {
        return $query->where('season', $season);
    }

    /**
     * Scope a query to only include rosters for the given week.
     */
    public function scopeForWeek(Builder $query, int|string $week): Builder
    {
        return $query->where('week', $week);
    }
}
