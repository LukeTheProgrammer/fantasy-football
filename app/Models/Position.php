<?php

namespace App\Models;

use App\Enums\NFLPositions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
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

    /**
     * Get the players for this position.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Scope for abbreviation prop.
     */
    public function scopeForAbbreviation(Builder $query, string|NFLPositions $abbreviation): Builder
    {
        $abb = ($abbreviation instanceof NFLPositions) ? $abbreviation->value : $abbreviation;

        return $query->where('abbreviation', '=', $abb);
    }
}
