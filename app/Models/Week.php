<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Week extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'date',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Get the season for this week.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * Scope query for current week.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope query for weeks in a season.
     */
    public function scopeForSeason(Builder $query, int $season): Builder
    {
        return $query->where('season_id', $season);
    }
}
