<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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

    /**
     * Get the players for this team.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function scopeForAbbreviation(Builder $query, string $abbreviation): Builder
    {
        return $query->where('abbreviation', '=', $abbreviation);
    }

    public function scopeForEspnId(Builder $query, int $espnId): Builder
    {
        return $query->where('espn_id', '=', $espnId);
    }

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
