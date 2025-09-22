<?php

namespace App\Models;

use App\Enums\TeamAbb;
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
     * The attributes that are not mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /* ===[ Relationships ]=== */

    /**
     * Get the players for this team.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope query by abbreviation.
     */
    public function scopeForAbbreviation(Builder $query, string|TeamAbb $abbreviation): Builder
    {
        return $query->where('abbreviation', '=', ($abbreviation instanceof TeamAbb) ? $abbreviation->value : $abbreviation);
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
