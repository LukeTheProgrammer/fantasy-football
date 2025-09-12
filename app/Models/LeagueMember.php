<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeagueMember extends Model
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
    protected $casts = [
        'is_admin'  => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the league that the member belongs to.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the user that the member represents.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the draft picks for the league member.
     */
    public function draftPicks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }

    /**
     * Get the draft picks for the league member.
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(LeagueMemberRoster::class);
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

    /**
     * Local scope method for filtering by external id.
     *
     * @param Builder $query
     * @param integer|string $extId
     *
     * @return Builder
     */
    public function scopeForExtId(Builder $query, int|string $extId): Builder
    {
        return $query->where('external_id', $extId);
    }
}
