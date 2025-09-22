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
        'year' => 'integer',
        'week' => 'integer',
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
     * Scope for year.
     */
    public function scopeForTeam(Builder $query, int|string|Team $team): Builder
    {
        return $query->where(function ($q) use ($team) {
            $q->orWhere('home_team_id', $team)
                ->orWhere('away_team_id', $team);
        });
    }

    /**
     * Scope for year.
     */
    public function scopeForYear(Builder $query, int|string $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope for week.
     */
    public function scopeForWeek(Builder $query, int|string $week): Builder
    {
        return $query->where('week', $week);
    }
}
