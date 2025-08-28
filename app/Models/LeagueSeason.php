<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeagueSeason extends Model
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
        'year' => 'integer',
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the league that owns the season.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the draft for this season.
     */
    public function draft(): HasOne
    {
        return $this->hasOne(Draft::class);
    }

    /**
     * Get the previous season.
     */
    public function previousSeason(): BelongsTo
    {
        return $this->belongsTo(LeagueSeason::class, 'previous_season_id');
    }

    /**
     * Get the next season.
     */
    public function nextSeason(): HasOne
    {
        return $this->hasOne(LeagueSeason::class, 'previous_season_id');
    }
}
