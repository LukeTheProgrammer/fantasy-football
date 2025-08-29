<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\League;
use App\Models\Player;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    /**
     * Display a listing of drafts for a league.
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('drafts/index', [
            'drafts' => $user->drafts()
                ->with(['league.members'])
                ->orderBy('draft_date', 'desc')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new draft.
     */
    public function create(League $league)
    {
        return Inertia::render('drafts/create', [
            'league' => $league,
        ]);
    }

    /**
     * Display the specified draft.
     */
    public function show(League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            abort(404, 'Draft does not belong to this league');
        }

        $draft->load([
            'picks.leagueMember.user',
            'picks.player.position',
            'picks.player.team',
        ]);

        // Get available players for drafting
        $draftedPlayerIds = $draft->picks()->whereNotNull('player_id')->pluck('player_id');
        $availablePlayers = Player::whereNotIn('id', $draftedPlayerIds)
            ->with(['position', 'team'])
            ->get();

        return Inertia::render('drafts/show', [
            'league' => $league,
            'draft' => $draft,
            'availablePlayers' => $availablePlayers,
        ]);
    }

    /**
     * Show the form for editing the specified draft.
     */
    public function edit(League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            abort(404, 'Draft does not belong to this league');
        }

        // Don't allow editing completed drafts
        if ($draft->is_completed) {
            return redirect()->route('leagues.drafts.show', [$league, $draft])
                ->with('error', 'Cannot edit a completed draft');
        }

        return Inertia::render('drafts/edit', [
            'league' => $league,
            'draft' => $draft,
        ]);
    }

    /**
     * Display the draft board for an active draft.
     */
    public function board(League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            abort(404, 'Draft does not belong to this league');
        }

        $draft->load([
            'picks.leagueMember.user',
            'picks.player.position',
            'picks.player.team',
        ]);

        // Get available players for drafting
        $draftedPlayerIds = $draft->picks()->whereNotNull('player_id')->pluck('player_id');
        $availablePlayers = Player::whereNotIn('id', $draftedPlayerIds)
            ->with(['position', 'team'])
            ->get();

        return Inertia::render('drafts/board', [
            'league' => $league,
            'draft' => $draft,
            'availablePlayers' => $availablePlayers,
        ]);
    }

    /**
     * Display the results of a completed draft.
     */
    public function results(League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            abort(404, 'Draft does not belong to this league');
        }

        $draft->load([
            'picks.leagueMember.user',
            'picks.player.position',
            'picks.player.team',
        ]);

        // Group picks by league member to show each team's draft results
        $teamResults = $draft->picks()
            ->whereNotNull('player_id')
            ->with(['leagueMember.user', 'player.position', 'player.team'])
            ->get()
            ->groupBy('league_member_id');

        return Inertia::render('drafts/results', [
            'league' => $league,
            'draft' => $draft,
            'teamResults' => $teamResults,
        ]);
    }
}
