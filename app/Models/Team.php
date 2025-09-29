<?php

namespace App\Models;

use App\Enums\NFLTeams;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are not mass assignable.
     *
     * @var list<string>
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
