<?php

namespace App\Models;

use App\Enums\NFLTeams;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     */
    public $incrementing = false;

    /**
     * {@inheritDoc}
     */
    public $keyType = 'string';

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /* ===[ Relationships ]=== */

    /**
     * Get the players for this team.
     */
    public function playerTeams(): HasMany
    {
        return $this->hasMany(PlayerTeam::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope query by abbreviation.
     */
    public function scopeNoFA(Builder $query): Builder
    {
        return $query->where('id', '!=', 'FA');
    }

    /**
     * Scope query by abbreviation.
     */
    public function scopeForAbbreviation(Builder $query, string|NFLTeams $abbreviation): Builder
    {
        return $query->where('abbreviation', '=', ($abbreviation instanceof NFLTeams) ? $abbreviation->value : $abbreviation);
    }

    /**
     * Scope query by ESPN ID.
     */
    public function scopeForEspnId(Builder $query, int $espnId): Builder
    {
        return $query->where('espn_id', '=', $espnId);
    }

    /* ===[ Attributes ]=== */

    /**
     * Get the full team name (location + name).
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->location} {$this->name}",
        );
    }
}
