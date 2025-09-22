<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Player extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'datetime',
        'draft_year' => 'datetime:Y',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Get the aliases for this player.
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(PlayerAlias::class);
    }

    /**
     * Get the draft picks for this player.
     */
    public function draftPicks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }

    /**
     * Get the draft rankings for this player.
     */
    public function draftRankings(): HasMany
    {
        return $this->hasMany(DraftRanking::class);
    }

    /**
     * Get the player projections for this player.
     */
    public function playerProjections(): HasMany
    {
        return $this->hasMany(PlayerProjection::class);
    }

    /**
     * Get the position for this player.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the team for this player.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope a query by position_id.
     *
     * @param Builder $query
     * @param integer|string|Position $position
     *
     * @return Builder
     */
    public function scopeForPosition(Builder $query, int|string|Position $position): Builder
    {
        return $query->where('position_id', ($position instanceof Position) ? $position->id : $position);
    }

    /**
     * Scope a query by team_id.
     *
     * @param Builder $query
     * @param integer|string|Team $team
     *
     * @return Builder
     */
    public function scopeForTeam(Builder $query, int|string|Team $team): Builder
    {
        return $query->where('team_id', ($team instanceof Team) ? $team->id : $team);
    }


    /**
     * Scope a query to only include players with the given ESPN ID.
     *
     * @param Builder $query
     * @param integer|string $espnId
     *
     * @return Builder
     */
    public function scopeEspnId(Builder $query, int|string $espnId): Builder
    {
        return $query->where('espn_id', $espnId);
    }

    /**
     * Queries players by name.
     *
     * @param Builder $query
     * @param string $name
     *
     * @return Builder
     */
    public function scopeNameLike(Builder $query, string $name): Builder
    {
        return $query->where(function ($q) use ($name) {
            $name = '%' . $name . '%';

            return $q->orWhere('first_name', 'like', $name)
                ->orWhere('last_name', 'like', $name)
                ->orWhere('full_name', 'like', $name);
        });
    }

    /* ===[ Attributes ]=== */

    /**
     * Get the player's age.
     */
    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birth_date?->age ?? null,
        );
    }

    /**
     * Check if the player is a rookie.
     */
    public function isRookie(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->draft_year?->gt(Carbon::now()->subYear()) ?? false,
        );
    }

    /**
     * Check if the player is a first-round pick.
     */
    public function isFirstRoundPick(): Attribute
    {
        return Attribute::make(
            get: fn () => (string) $this->draft_round === '1',
        );
    }
}
