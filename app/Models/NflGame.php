<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NflGame extends Model
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
        'year' => 'integer',
        'week' => 'integer',
    ];

    /* ===[ Relationships ]=== */

    /**
     * Relation to home team.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relation to away team.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relation to fantasy points weeks.
     */
    public function fantasyPointsWeeks(): HasMany
    {
        return $this->hasMany(FantasyPointsWeek::class);
    }
}
