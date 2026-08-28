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
     *
     * Pass the format being asked for whenever the caller knows it. That turns
     * the lookup into one scalar over a single slice; without it the newest
     * date has to be worked out for every format at once, which is the same
     * answer for far more work.
     */
    public function scopeLatestRanking(
        Builder $query,
        int $season,
        ?float $ppr = null,
        ?bool $superflex = null,
        ?string $type = null,
        ?string $source = null
    ): Builder {
        // Qualified: the all-formats branch joins the table to itself, so a
        // bare column name is ambiguous there.
        $query->where('draft_rankings.season', $season);

        if ($ppr === null && $superflex === null && $source === null) {
            return $this->latestPerFormat($query, $season);
        }

        $latest = DraftRanking::query()->where('season', $season);

        foreach (compact('ppr', 'superflex', 'type', 'source') as $column => $value) {
            if ($value !== null) {
                $latest->where($column, $value);
            }
        }

        return $query->where('ranked_at', $latest->max('ranked_at'));
    }

    /**
     * The newest date held for each source and format, joined back on so a
     * caller that spans formats still sees only current rows.
     */
    private function latestPerFormat(Builder $query, int $season): Builder
    {
        $latest = DraftRanking::query()
            ->selectRaw('season, type, ppr, superflex, source, max(ranked_at) as ranked_at')
            ->where('season', $season)
            ->groupBy('season', 'type', 'ppr', 'superflex', 'source');

        return $query
            ->select('draft_rankings.*')
            ->joinSub($latest, 'latest', function ($join) {
                foreach (['season', 'type', 'ppr', 'superflex', 'source', 'ranked_at'] as $column) {
                    $join->on("latest.{$column}", '=', "draft_rankings.{$column}");
                }
            });
    }

    /**
     * Scope a query to order by rank (lowest to highest).
     */
    public function scopeOrderByRank(Builder $query): Builder
    {
        return $query->orderBy('rank');
    }
}
