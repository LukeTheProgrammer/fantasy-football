<?php

namespace App\Policies;

use App\Models\League;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaguePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the league.
     */
    public function view(User $user, League $league): bool
    {
        // Users can view leagues they are members of or public leagues
        return $league->is_public || $league->userIsMember($user);
    }

    /**
     * Determine whether the user can update the league.
     */
    public function update(User $user, League $league): bool
    {
        // Only league admins can update the league
        return $league->userIsAdmin($user);
    }

    /**
     * Determine whether the user can delete the league.
     */
    public function delete(User $user, League $league): bool
    {
        // Only the league creator can delete the league
        return $league->userIsCreator($user);
    }
}
