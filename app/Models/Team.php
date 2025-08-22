<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $abbreviation
 * @property string $location
 * @property string $name
 * @property string|null $logo
 * @property string $conference
 * @property string $division
 * @property int|null $espn_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static Builder<static>|Team newModelQuery()
 * @method static Builder<static>|Team newQuery()
 * @method static Builder<static>|Team query()
 * @method static Builder<static>|Team whereAbbreviation($value)
 * @method static Builder<static>|Team whereConference($value)
 * @method static Builder<static>|Team whereCreatedAt($value)
 * @method static Builder<static>|Team whereDeletedAt($value)
 * @method static Builder<static>|Team whereDivision($value)
 * @method static Builder<static>|Team whereEspnId($value)
 * @method static Builder<static>|Team whereId($value)
 * @method static Builder<static>|Team whereLocation($value)
 * @method static Builder<static>|Team whereLogo($value)
 * @method static Builder<static>|Team whereName($value)
 * @method static Builder<static>|Team whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Team extends Model
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
     * Get the players for this team.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the full team name (location + name).
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->location} {$this->name}",
        );
    }
}
