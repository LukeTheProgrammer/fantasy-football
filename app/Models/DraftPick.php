<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftPick extends Model
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
        'pick_number' => 'integer',
        'round' => 'integer',
        'amount' => 'decimal:2',
        'is_keeper' => 'boolean',
        'previous_year_cost' => 'decimal:2',
        'pick_time' => 'datetime',
    ];

    /**
     * Get the draft that owns the pick.
     */
    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    /**
     * Get the league member that owns the pick.
     */
    public function leagueMember(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class);
    }

    /**
     * Get the player that was picked.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
