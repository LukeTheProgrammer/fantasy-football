<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTeam extends Model
{
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
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /* ===[ Scopes ]=== */

    public function scopeForPlayer(Builder $query, int|string|Player $player): Builder
    {
        return $query->where('player_id', $player instanceof Player ? $player->id : $player);
    }

    public function scopeForTeam(Builder $query, int|string|Team $team): Builder
    {
        return $query->where('team_id', $team instanceof Team ? $team->id : $team);
    }
}
