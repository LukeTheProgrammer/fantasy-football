<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $espn_id
 * @property int $position_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $height
 * @property string|null $weight
 * @property string|null $college
 * @property string|null $draft_year
 * @property string|null $draft_round
 * @property string|null $draft_pick
 * @property string|null $draft_team
 * @property string|null $birth_date
 * @property string|null $headshot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftPick($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereEspnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereHeadshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereWeight($value)
 *
 * @mixin \Eloquent
 */
class Player extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'datetime',
    ];

    /**
     * Get the position for this player.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the team for this player.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the player's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the player's age.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /**
     * Check if the player is a rookie.
     */
    public function getIsRookieAttribute(): bool
    {
        if (! $this->draft_year) {
            return false;
        }

        return $this->draft_year >= now()->year - 1;
    }

    /**
     * Check if the player is a first-round pick.
     */
    public function getIsFirstRoundPickAttribute(): bool
    {
        return $this->draft_round === '1';
    }
}
