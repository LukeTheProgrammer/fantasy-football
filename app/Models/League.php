<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class League extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'max_teams',
        'is_public',
        'join_code',
        'draft_type',
        'draft_date',
        'is_active',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'draft_date' => 'datetime',
    ];
    
    /**
     * Get the user who created the league.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the league settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(LeagueSettings::class);
    }
    
    /**
     * Get the league members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(LeagueMember::class);
    }
}
