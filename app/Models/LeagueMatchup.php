<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeagueMatchup extends Model
{
    use HasFactory;
    use SoftDeletes;

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
    protected $casts = [];

    /**
     * Get the league that the member belongs to.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the draft picks for the league member.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class, 'home_member_id', 'id');
    }

    /**
     * Get the rosters for the league member.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class, 'away_member_id', 'id');
    }

    /**
     * Local scope method for filtering by league.
     *
     * @param Builder $query
     * @param integer|string|League $league
     *
     * @return Builder
     */
    public function scopeForLeague(Builder $query, int|string|League $league): Builder
    {
        return $query->where('league_id', $league instanceof League ? $league->id : $league);
    }
}
