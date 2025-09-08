<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $player_id
 * @property int $year
 * @property string $source
 * @property int $ranking
 * @property int|null $tier
 * @property float|null $adp
 * @property float|null $value
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Player $player
 *
 * @method static Builder<static>|DraftRanking newModelQuery()
 * @method static Builder<static>|DraftRanking newQuery()
 * @method static Builder<static>|DraftRanking query()
 * @method static Builder<static>|DraftRanking whereId($value)
 * @method static Builder<static>|DraftRanking wherePlayerId($value)
 * @method static Builder<static>|DraftRanking whereYear($value)
 * @method static Builder<static>|DraftRanking whereSource($value)
 * @method static Builder<static>|DraftRanking whereRanking($value)
 * @method static Builder<static>|DraftRanking whereTier($value)
 * @method static Builder<static>|DraftRanking whereAdp($value)
 * @method static Builder<static>|DraftRanking whereValue($value)
 * @method static Builder<static>|DraftRanking whereNotes($value)
 * @method static Builder<static>|DraftRanking whereCreatedAt($value)
 * @method static Builder<static>|DraftRanking whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
        'year' => 'integer',
        'ranking' => 'integer',
        'tier' => 'integer',
        'adp' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    /**
     * Get the player that this ranking belongs to.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Scope a query to only include rankings for a specific year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to only include rankings from a specific source.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope a query to order by ranking (lowest to highest).
     */
    public function scopeOrderByRanking(Builder $query): Builder
    {
        return $query->orderBy('ranking');
    }
}
