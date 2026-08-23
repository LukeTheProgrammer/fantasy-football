<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRankingAverage extends Model
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
        'season'    => 'integer',
        'ranked_on' => 'date',
        'ppr'       => 'decimal:2',
        'superflex' => 'boolean',
        'rank'      => 'decimal:2',
        'tier'      => 'decimal:2',
        'adp'       => 'decimal:2',
        'adv'       => 'decimal:2',
    ];

    /**
     * Get the player that this average belongs to.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Scope a query to only include averages for a specific season.
     */
    public function scopeForSeason(Builder $query, int $season): Builder
    {
        return $query->where('season', $season);
    }

    /**
     * Scope a query to a single scoring format.
     */
    public function scopeForFormat(
        Builder $query,
        string $type = 'redraft',
        float $ppr = 0,
        bool $superflex = false
    ): Builder {
        return $query->where('type', $type)
            ->where('ppr', $ppr)
            ->where('superflex', $superflex);
    }

    /**
     * Scope a query to order by rank (lowest to highest).
     */
    public function scopeOrderByRank(Builder $query): Builder
    {
        return $query->orderBy('rank');
    }
}
