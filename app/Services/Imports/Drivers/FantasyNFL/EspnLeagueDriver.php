<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Facades\Data;
use App\Facades\Espn;
use App\Facades\Player as PlayerFacade;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMatchup;
use App\Models\LeagueMember;
use App\Models\Player;
use App\Models\Season;
use App\Models\User;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EspnLeagueDriver
{
    private User $creator;

    private ?CredentialsData $credentials = null;

    private array $leagueData = [];

    /**
     * Draft picks whose player could not be resolved.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $skippedPicks = [];

    public function __construct(private array $metaData = [])
    {
        $this->creator = User::findOrFail(Arr::get($metaData, 'created_by_user_id'));

        $this->credentials = CredentialsData::from([
            'leagueId' => Arr::get($metaData, 'league_id'),
            's2'       => Arr::get($metaData, 's2'),
            'swid'     => Arr::get($metaData, 'swid'),
        ]);

        if (!$this->credentials instanceof CredentialsData) {
            throw new InvalidArgumentException('Invalid credentials');
        }
    }

    public function import(): League
    {
        $this->loadData();

        $league = $this->createLeague();

        Data::espn()->getFantasyLeagueRosters($league, $league->season_id);

        return $league;
    }

    //

    private function loadData()
    {
        $this->leagueData = Data::espn()->getFantasyLeague(
            credentials: $this->credentials,
            season: Arr::get($this->metaData, 'season')
        );
    }

    private function createLeague(): League
    {
        $leagueData = $this->leagueData['league'];

        $leagueData['created_by_user_id'] = $this->creator->id;
        $leagueData['platform_id'] = $this->credentials->leagueId;
        $leagueData['credentials'] = $this->credentials;

        // leagues.season_id is a key into seasons, and ESPN publishes a new
        // season before the season list is refreshed, so the year is made to
        // exist rather than failing the import over it.
        Season::firstOrCreate(['id' => $leagueData['season']], ['is_current' => false]);

        // The formatter speaks in seasons; the column is the key into them.
        $leagueData['season_id'] = Arr::pull($leagueData, 'season');

        // Season is part of the key: ESPN reuses a league id year over year, so
        // without it a new season overwrites the previous season's league row.
        $league = League::updateOrCreate(
            [
                'platform'    => $leagueData['platform'],
                'platform_id' => $leagueData['platform_id'],
                'season_id'   => $leagueData['season_id'],
            ],
            $leagueData,
        );

        $this->createSettings($league);

        $this->createMembers($league);

        $this->createRosters($league);

        $this->createDraft($league);

        $this->createMatchups($league);

        return $league;
    }

    private function createSettings(League $league)
    {
        $league->settings()->updateOrCreate(
            ['league_id' => $league->id],
            $this->leagueData['settings'],
        );
    }

    private function createMembers(League $league)
    {
        foreach ($this->leagueData['members'] as $member) {
            $league->members()->updateOrCreate(
                ['external_id' => $member['external_id']],
                $member,
            );
        }
    }

    private function createRosters(League $league)
    {
        $members = LeagueMember::forLeague($league)->get()->keyBy('external_id');

        foreach ($this->leagueData['roster'] as $roster) {
            $member = $members->get($roster['team_id']);

            if (!$member instanceof LeagueMember) {
                continue;
            }
        }
    }

    private function createDraft(League $league)
    {
        $draft = $league->draft()->updateOrCreate(
            ['league_id' => $league->id],
            $this->leagueData['draft'],
        );

        $draft->picks()->delete();

        foreach ($this->leagueData['draftPicks'] as $pick) {
            $member = LeagueMember::forLeague($league)->forExtId($pick['league_member_id'])->first();

            if (!$member instanceof LeagueMember) {
                Log::error('Member not found for draft pick', $pick);

                continue;
            }

            // ESPN's fantasy id is not an athlete id when the pick is a team
            // defense, so it is translated before it is looked up.
            $player = PlayerFacade::find(
                Espn::playerLookup($pick['player_id'], Arr::get($pick, 'full_name')),
                ['source' => static::class]
            );

            if (!$player instanceof Player) {
                // A dropped pick is a hole in the draft this app exists to read,
                // so it is counted and reported rather than only logged.
                $this->skippedPicks[] = $pick;

                Log::error('Player not found for draft pick', $pick);

                continue;
            }

            $find = [
                'draft_id'         => $draft->id,
                'league_member_id' => $member->id,
                'player_id'        => $player->id,
            ];

            $update = [
                'round'               => $pick['round'],
                'pick_number'         => $pick['pick_number'],
                'overall_pick_number' => $pick['overall_pick_number'],
                'amount'              => $pick['amount'],
                'is_keeper'           => $pick['is_keeper'],
            ];

            DraftPick::updateOrCreate($find, $update);
        }
    }

    private function createMatchups(League $league)
    {
        foreach ($this->leagueData['schedules'] as $matchup) {
            if (empty($matchup['home_member_id'])) {
                continue;
            }

            $homeMember = LeagueMember::forLeague($league)->forExtId($matchup['home_member_id'])->first();

            if (!$homeMember instanceof LeagueMember) {
                Log::error('Member not found for home team id', $matchup);

                continue;
            }

            // A first round bye is a real game with one team in it, so it is
            // kept with no opponent rather than dropped: a bracket that hides
            // the bye cannot show who earned it.
            $awayMember = empty($matchup['away_member_id'])
                ? null
                : LeagueMember::forLeague($league)->forExtId($matchup['away_member_id'])->first();

            if (!empty($matchup['away_member_id']) && !$awayMember instanceof LeagueMember) {
                Log::error('Member not found for away team id', $matchup);

                continue;
            }

            $find = [
                'league_id'      => $league->id,
                'season'         => $matchup['season'],
                'week'           => $matchup['week'],
                'home_member_id' => $homeMember->id,
                'away_member_id' => $awayMember?->id,
            ];

            $update = array_filter([
                'playoff_tier'         => Arr::get($matchup, 'playoff_tier'),
                'winner'               => Arr::get($matchup, 'winner'),
                'home_score'           => Arr::get($matchup, 'home_score'),
                'away_score'           => Arr::get($matchup, 'away_score'),
                'home_projected_score' => Arr::get($matchup, 'home_projected_score'),
                'away_projected_score' => Arr::get($matchup, 'away_projected_score'),
            ]);

            LeagueMatchup::updateOrCreate($find, $update);
        }
    }
}
