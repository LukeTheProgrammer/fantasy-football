<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NflGame extends Model
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
        'season' => 'integer',
        'week'   => 'integer',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Relation to home team.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relation to away team.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the player projections for this player.
     */
    public function playerProjections(): HasMany
    {
        return $this->hasMany(PlayerProjection::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope to a Team.
     */
    public function scopeForTeam(Builder $query, int|string|Team $team): Builder
    {
        return $query->where(function ($q) use ($team) {
            $q->orWhere('home_team_id', ($team instanceof Team) ? $team->id : $team)
                ->orWhere('away_team_id', ($team instanceof Team) ? $team->id : $team);
        });
    }

    /**
     * Scope to a season.
     */
    public function scopeForSeason(Builder $query, int|string|Season $season): Builder
    {
        return $query->where('season', ($season instanceof Season) ? $season->id : $season);
    }

    /**
     * Scope for week.
     */
    public function scopeForWeek(Builder $query, int|string|Week $week): Builder
    {
        return $query->where('week', ($week instanceof Week) ? $week->week : $week);
    }
}
