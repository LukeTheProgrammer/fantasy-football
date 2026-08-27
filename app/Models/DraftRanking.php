<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftRanking extends Model
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
        'ranked_at' => 'date',
        'ppr'       => 'decimal:2',
        'superflex' => 'boolean',
        'rank'      => 'integer',
        'tier'      => 'integer',
        'adp'       => 'decimal:2',
        'adv'       => 'decimal:2',
    ];

    /**
     * Get the player that this ranking belongs to.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Scope a query to only include rankings for a specific season.
     */
    public function scopeForSeason(Builder $query, int $season): Builder
    {
        return $query->where('season', $season);
    }

    /**
     * Scope a query to only include rankings from a specific source.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope a query to one scoring format.
     */
    public function scopeForFormat(
        Builder $query,
        float $ppr = 0,
        bool $superflex = false,
        string $type = 'redraft'
    ): Builder {
        return $query->where('type', $type)
            ->where('ppr', $ppr)
            ->where('superflex', $superflex);
    }

    /**
     * The scoring formats held for a season, as ppr and superflex pairs.
     */
    public function scopeAvailableFormats(Builder $query, int $season, string $type = 'redraft'): Builder
    {
        return $query->where('season', $season)
            ->where('type', $type)
            ->select(['ppr', 'superflex'])
            ->distinct();
    }

    /**
     * Scope a query to the newest rankings held for a season.
     *
     * Newest is per source and format, not across the table: sources publish on
     * their own schedules, so one source importing today would otherwise hide
     * every format it does not publish.
     */
    public function scopeLatestRanking(Builder $query, int $season): Builder
    {
        return $query->where('season', $season)
            ->where('ranked_at', fn ($latest) => $latest->selectRaw('max(ranked_at)')
                ->from('draft_rankings as latest')
                ->whereColumn('latest.season', 'draft_rankings.season')
                ->whereColumn('latest.type', 'draft_rankings.type')
                ->whereColumn('latest.ppr', 'draft_rankings.ppr')
                ->whereColumn('latest.superflex', 'draft_rankings.superflex')
                ->whereColumn('latest.source', 'draft_rankings.source'));
    }

    /**
     * Scope a query to order by rank (lowest to highest).
     */
    public function scopeOrderByRank(Builder $query): Builder
    {
        return $query->orderBy('rank');
    }
}
