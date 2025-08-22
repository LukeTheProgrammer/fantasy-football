<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static Builder<static>|Player newModelQuery()
 * @method static Builder<static>|Player newQuery()
 * @method static Builder<static>|Player query()
 * @method static Builder<static>|Player whereBirthDate($value)
 * @method static Builder<static>|Player whereCollege($value)
 * @method static Builder<static>|Player whereCreatedAt($value)
 * @method static Builder<static>|Player whereDeletedAt($value)
 * @method static Builder<static>|Player whereDraftPick($value)
 * @method static Builder<static>|Player whereDraftRound($value)
 * @method static Builder<static>|Player whereDraftTeam($value)
 * @method static Builder<static>|Player whereDraftYear($value)
 * @method static Builder<static>|Player whereEspnId($value)
 * @method static Builder<static>|Player whereFirstName($value)
 * @method static Builder<static>|Player whereHeadshot($value)
 * @method static Builder<static>|Player whereHeight($value)
 * @method static Builder<static>|Player whereId($value)
 * @method static Builder<static>|Player whereLastName($value)
 * @method static Builder<static>|Player wherePositionId($value)
 * @method static Builder<static>|Player whereUpdatedAt($value)
 * @method static Builder<static>|Player whereWeight($value)
 *
 * @mixin \Eloquent
 */
class Player extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'draft_year' => 'datetime:Y',
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
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Get the player's age.
     */
    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birth_date?->age ?? null,
        );
    }

    /**
     * Check if the player is a rookie.
     */
    public function isRookie(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->draft_year?->gt(Carbon::now()->subYear()) ?? false,
        );
    }

    /**
     * Check if the player is a first-round pick.
     */
    public function isFirstRoundPick(): Attribute
    {
        return Attribute::make(
            get: fn () => (string) $this->draft_round === '1',
        );
    }
}
