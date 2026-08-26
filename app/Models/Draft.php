<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Draft extends Model
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
        'draft_date'     => 'datetime',
        'is_completed'   => 'boolean',
        'is_active'      => 'boolean',
        'auction_budget' => 'integer',
        'time_per_pick'  => 'integer',
    ];

    /**
     * Get the league that owns the draft.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the picks for the draft.
     */
    public function picks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }
}
