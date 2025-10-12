<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftPick extends Model
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
        'pick_number' => 'integer',
        'round' => 'integer',
        'amount' => 'decimal:2',
        'is_keeper' => 'boolean',
        'previous_year_cost' => 'decimal:2',
        'pick_time' => 'datetime',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Get the draft that owns the pick.
     */
    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    /**
     * Get the league member that owns the pick.
     */
    public function leagueMember(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class);
    }

    /**
     * Get the player that was picked.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /* ===[ Scopes ]=== */

    public function scopeForDraft(Builder $query, Draft|int|string $draft): Builder
    {
        return $query->where('draft_id', $draft instanceof Draft ? $draft->id : $draft);
    }

    public function scopeForLeagueMember(Builder $query, LeagueMember|int|string $member): Builder
    {
        return $query->where('league_member_id', $member instanceof LeagueMember ? $member->id : $member);
    }

    public function scopeForPlayer(Builder $query, Player|int|string $player): Builder
    {
        return $query->where('player_id', $player instanceof Player ? $player->id : $player);
    }
}
