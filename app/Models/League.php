<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class League extends Model
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
        'is_public'   => 'boolean',
        'is_active'   => 'boolean',
        'draft_date'  => 'datetime',
        'credentials' => 'array',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Get the user who created the league.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the draft for the league.
     */
    public function draft(): HasOne
    {
        return $this->hasOne(Draft::class);
    }

    /**
     * Get the league members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(LeagueMember::class);
    }

    /**
     * Get the league matchups.
     */
    public function matchups(): HasMany
    {
        return $this->hasMany(LeagueMatchup::class);
    }

    /**
     * Get the associated season.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the league settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(LeagueSettings::class);
    }

    /* ===[ Scopes ]=== */

    /**
     * The same league across every season. A league is identified by its
     * platform and platform id; the season is what makes each row distinct.
     */
    public function scopeSameLeagueAs(Builder $query, League $league): Builder
    {
        return $query->where('platform', $league->platform)
            ->where('platform_id', $league->platform_id);
    }

    #[Scope]
    protected function forUser(Builder $query, User|int|string $user): Builder
    {
        return $query->whereHas('members', fn ($q) => $q->forUser($user));
    }

    /* ===[ Helpers ]=== */

    /**
     * Checks if a user is the Creator of this league.
     *
     * @param User $user
     *
     * @return bool
     */
    public function userIsCreator(User $user): bool
    {
        return $this->creator_id === $user->id;
    }

    /**
     * Checks if a user is an admin of this league.
     *
     * @param User $user
     *
     * @return bool
     */
    public function userIsAdmin(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('is_admin', true)->exists();
    }

    /**
     * Checks if a user is a member of this league.
     *
     * @param User $user
     *
     * @return bool
     */
    public function userIsMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
