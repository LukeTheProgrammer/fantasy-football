<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Position extends Model
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
     * Get the players for this position.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function scopeForAbbreviation(Builder $query, string $abbreviation): Builder
    {
        return $query->where('abbreviation', '=', $abbreviation);
    }
}
