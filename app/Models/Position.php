<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $abbreviation
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static Builder<static>|Position newModelQuery()
 * @method static Builder<static>|Position newQuery()
 * @method static Builder<static>|Position query()
 * @method static Builder<static>|Position whereAbbreviation($value)
 * @method static Builder<static>|Position whereCreatedAt($value)
 * @method static Builder<static>|Position whereDeletedAt($value)
 * @method static Builder<static>|Position whereId($value)
 * @method static Builder<static>|Position whereName($value)
 * @method static Builder<static>|Position whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Position extends Model
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
     * Get the players for this position.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
