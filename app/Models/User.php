<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Get the leagues created by the user.
     *
     * @return HasMany
     */
    public function createdLeagues()
    {
        return $this->hasMany(League::class, 'created_by');
    }

    /**
     * Get the league memberships for the user.
     *
     * @return HasMany
     */
    public function leagueMemberships()
    {
        return $this->hasMany(LeagueMember::class);
    }

    /**
     * Get all leagues the user is a member of.
     *
     * @return BelongsToMany
     */
    public function leagues()
    {
        return $this->belongsToMany(League::class, 'league_members')
            ->withPivot(['team_name', 'team_logo', 'is_admin', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get all drafts the user has access to through their league memberships.
     *
     * @return HasManyThrough
     */
    public function drafts()
    {
        return $this->hasManyThrough(
            Draft::class,
            LeagueMember::class,
            'user_id', // Foreign key on LeagueMember table
            'league_id', // Foreign key on Draft table
            'id', // Local key on User table
            'league_id' // Local key on LeagueMember table
        );
    }
}
