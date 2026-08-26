<?php

namespace App\Models;

use App\Observers\PlayerAliasObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(PlayerAliasObserver::class)]
class PlayerAlias extends Model
{
    use HasFactory;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /* ===[ Relationships ]=== */

    /**
     * Get the player that this alias belongs to.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_ulid', 'ulid');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /* ===[ Scopes ]=== */

    public function scopeForName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    /**
     * Match on the reduced form of a name, so a suffix or a stray apostrophe
     * does not hide an alias that was already learned.
     */
    public function scopeForNormalizedName(Builder $query, string $name): Builder
    {
        return $query->where('normalized_name', $name);
    }

    public function scopeNameLike(Builder $query, string $name): Builder
    {
        $like = '%' . $name . '%';

        return $query->where('name', 'like', $like);
    }
}
