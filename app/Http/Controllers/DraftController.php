<?php

namespace App\Http\Controllers;

use App\Facades\Auction as AuctionFacade;
use App\Models\Draft;
use App\Models\DraftRanking;
use App\Models\League;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DraftController extends Controller
{
    /**
     * Display a listing of drafts for a league.
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('drafts/DraftsIndexPage', [
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
        return Inertia::render('drafts/CreateDraftPage', [
            'league' => $league,
        ]);
    }

    /**
     * Display the specified draft.
     */
    /**
     * One league's draft for a season.
     *
     * The league in the URL only has to be any season of the league: the season
     * segment picks the row, so a link keeps working when it is followed a year
     * later.
     */
    public function show(League $league, int $season)
    {
        $draft = League::sameLeagueAs($league)
            ->where('season', $season)
            ->firstOrFail()
            ->draft;

        if (!$draft instanceof Draft) {
            abort(404, 'This league has no draft for that season');
        }

        $draft->load([
            'league.members.user',
            'league.settings',
            'picks' => [
                'leagueMember.user',
                'player' => [
                    'position',
                    'team',
                ],
            ],
        ]);

        // The budget plan is personal and only means anything in an auction,
        // so it is only sent when the signed in user has a team to plan for.
        $member = $draft->league->members->firstWhere('user_id', Auth::id());

        return Inertia::render('drafts/ShowDraftPage', [
            'draft'   => $draft,
            'seasons' => $this->seasonOptions($draft),
            'rosters' => AuctionFacade::rosters($draft),
            'budget'  => $draft->draft_type === 'auction' && $member
                ? AuctionFacade::budget($draft, $member)
                : null,
        ]);
    }

    /**
     * The seasons this league has drafted, newest first, each pointing at the
     * league row for that season. Seasons with no draft row are omitted.
     */
    private function seasonOptions(Draft $draft): Collection
    {
        return League::sameLeagueAs($draft->league)
            ->with('draft')
            ->orderByDesc('season')
            ->get()
            ->filter(fn (League $league) => $league->draft !== null)
            ->map(fn (League $league) => [
                'id'     => $league->id,
                'season' => $league->season,
            ])
            ->values();
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
            return redirect()->route('drafts.show', [$draft->league_id, $draft->league->season])
                ->with('error', 'Cannot edit a completed draft');
        }

        return Inertia::render('drafts/EditDraftPage', [
            'league' => $league,
            'draft'  => $draft,
        ]);
    }

    /**
     * Display the draft board for an active draft.
     */
    public function draftRoom(Draft $draft)
    {
        $draft->load([
            'league.settings',
            'league.members.user',
            'picks' => [
                'leagueMember.user',
                'player' => [
                    'position',
                    'team',
                ],
            ],
        ]);

        // An auction board and a snake board are different tools, so they are
        // different pages rather than one page with branches inside it.
        if ($draft->draft_type === 'auction') {
            return $this->auctionDraftRoom($draft);
        }

        // The newest rankings in a single scoring format, so a player appears
        // once rather than once per format.
        [$ppr, $superflex] = $this->rankingFormat($draft->league);

        $availablePlayers = DraftRanking::query()
            ->latestRanking($draft->league->season)
            ->forFormat($ppr, $superflex)
            ->where(function ($q) {
                $q->where('rank', '>', 0)
                    ->orWhere('adp', '>', 0)
                    ->orWhere('adv', '>', 0);
            })
            ->with(['player.position', 'player.team'])
            ->orderByRank()
            ->get();

        return Inertia::render('drafts/DraftRoomPage', [
            'draft'            => $draft,
            'availablePlayers' => $availablePlayers,
        ]);
    }

    /**
     * The auction cheat sheet: every rankable player with both value estimates,
     * and what each team can still spend.
     */
    private function auctionDraftRoom(Draft $draft)
    {
        // The budget is personal, so it only exists when the signed in user
        // actually has a team in this league.
        $member = $draft->league->members->firstWhere('user_id', Auth::id());

        return Inertia::render('drafts/AuctionDraftRoomPage', [
            'draft'   => $draft,
            'players' => AuctionFacade::cheatSheet($draft),
            'teams'   => AuctionFacade::teams($draft),
            'rosters' => AuctionFacade::rosters($draft),
            'budget'  => $member ? AuctionFacade::budget($draft, $member) : null,
        ]);
    }

    /**
     * The closest scoring format to this league's own that rankings were
     * actually published in.
     *
     * Sources do not publish every combination. Superflex changes a draft board
     * far more than a half point per reception does, so it is matched first.
     *
     * @return array{0: float, 1: bool}
     */
    private function rankingFormat(League $league): array
    {
        $ppr = $league->settings?->pprValue() ?? 0.0;
        $superflex = (bool) $league->settings?->two_qb;

        $available = DraftRanking::query()
            ->availableFormats($league->season)
            ->get()
            ->map(fn (DraftRanking $ranking) => [(float) $ranking->ppr, (bool) $ranking->superflex]);

        if ($available->isEmpty()) {
            return [$ppr, $superflex];
        }

        $preferences = [
            fn ($format) => $format === [$ppr, $superflex],
            fn ($format) => $superflex && $format[1] === true,
            fn ($format) => $format[0] === $ppr && $format[1] === false,
        ];

        foreach ($preferences as $matches) {
            $match = $available->first($matches);

            if ($match) {
                return $match;
            }
        }

        return $available->first();
    }

    /**
     * Display the results of a completed draft.
     */
    public function results(Draft $draft)
    {
        $draft->load([
            'league',
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

        return Inertia::render('drafts/DraftResultsPage', [
            'draft'       => $draft,
            'teamResults' => $teamResults,
        ]);
    }
}
