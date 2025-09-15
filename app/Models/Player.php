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
     * Get the seasonal fantasy points for this player.
     */
    public function fantasyPointsSeasons(): HasMany
    {
        return $this->hasMany(FantasyPointsSeason::class);
    }

    /**
     * Get the weekly fantasy points for this player.
     */
    public function fantasyPointsWeeks(): HasMany
    {
        return $this->hasMany(FantasyPointsWeek::class);
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

    /**
     * Get the player's current draft rankings.
     */
    public function currentDraftRankings(): HasMany
    {
        return $this->draftRankings()->where('draft_year', Carbon::now()->year);
    }

    /* ===[ Scopes ]=== */

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
