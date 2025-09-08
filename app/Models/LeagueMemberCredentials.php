<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeagueMemberCredentials extends Model
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
        'espn_s2'  => 'encrypted',
        'espn_swid' => 'encrypted',
    ];

    /**
     * Get the league member that the credentials belong to.
     */
    public function leagueMember(): BelongsTo
    {
        return $this->belongsTo(LeagueMember::class);
    }
}
