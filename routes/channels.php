<?php

use App\Models\Draft;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// The live draft board. Anyone who may look at the draft may watch picks
// arrive on it, which is the same rule the room itself is opened under.
Broadcast::channel('draft.{draft}', function (User $user, Draft $draft) {
    return Gate::forUser($user)->allows('view', $draft);
});
