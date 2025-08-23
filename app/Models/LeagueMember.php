<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueMember extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'league_id',
        'user_id',
        'team_name',
        'team_logo',
        'draft_position',
        'is_admin',
        'is_active',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the league that the member belongs to.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
    
    /**
     * Get the user that the member represents.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
