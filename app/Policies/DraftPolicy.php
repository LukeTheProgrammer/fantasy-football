<?php

namespace App\Policies;

use App\Models\Draft;
use App\Models\User;

class DraftPolicy
{
    /**
     * Determine whether the user can view the draft room.
     */
    public function view(User $user, Draft $draft): bool
    {
        return $draft->league->userIsMember($user);
    }

    /**
     * Determine whether the user can record what happened in the draft.
     *
     * The draft room is a personal cheat sheet rather than the draft itself,
     * so any member of the league may record results into their own board.
     */
    public function record(User $user, Draft $draft): bool
    {
        return $draft->league->userIsMember($user);
    }
}
