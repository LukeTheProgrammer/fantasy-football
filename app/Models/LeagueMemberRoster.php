<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeagueMemberRoster extends Model
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
        'added_at'   => 'datetime',
        'dropped_at' => 'datetime',
    ];

    /**
     * Get the league that the member belongs to.
     */
    public function leagueMember(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class);
    }

    /**
     * Get the user that the member represents.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
