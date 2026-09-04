<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Facades\Data;
use App\Facades\Player as PlayerFacade;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\Player;
use App\Models\Season;
use App\Models\User;
use App\Services\CBS\CBSConstants;
use App\Services\CBS\Data\FantasyNFL\CredentialsData;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CbsLeagueDriver
{
    private User $creator;

    private CredentialsData $credentials;

    private array $leagueData = [];

    /**
     * Keepers whose player could not be resolved.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $skippedKeepers = [];

    public function __construct(private array $metaData = [])
    {
        $this->creator = User::findOrFail(Arr::get($metaData, 'created_by_user_id'));

        $this->credentials = CredentialsData::from([
            'leagueId'    => Arr::get($metaData, 'league_id'),
            'accessToken' => Arr::get($metaData, 'access_token'),
        ]);
    }

    public function import(): League
    {
        $this->loadData();

        return $this->createLeague();
    }

    //

    private function season(): int
    {
        return (int) (Arr::get($this->metaData, 'season') ?: date('Y'));
    }

    private function loadData(): void
    {
        $this->leagueData = Data::cbs()->getFantasyLeague(
            credentials: $this->credentials,
            season: $this->season()
        );
    }

    private function createLeague(): League
    {
        $leagueData = $this->leagueData['league'];

        $leagueData['created_by_user_id'] = $this->creator->id;
        $leagueData['platform_id'] = $this->credentials->leagueId;
        $leagueData['credentials'] = $this->credentials;

        Season::firstOrCreate(['id' => $leagueData['season']], ['is_current' => false]);

        $leagueData['season_id'] = Arr::pull($leagueData, 'season');

        // CBS reuses a league id year over year, so the season is part of the
        // key or a new season overwrites the previous one.
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

        $this->createDraft($league);

        $this->createKeepers($league);

        return $league;
    }

    private function createSettings(League $league): void
    {
        $league->settings()->updateOrCreate(
            ['league_id' => $league->id],
            $this->leagueData['settings'],
        );
    }

    private function createMembers(League $league): void
    {
        $ownTeamId = Arr::get($this->leagueData, 'ownTeamId');

        foreach ($this->leagueData['members'] as $member) {
            // The operator's own team is linked to their account, or nothing
            // in the app can tell which of the twelve teams is theirs — and
            // the draft room only lets a member of the league record a pick.
            if ($ownTeamId !== null && (string) $member['external_id'] === (string) $ownTeamId) {
                $member['user_id'] = $this->creator->id;
            }

            $league->members()->updateOrCreate(
                ['external_id' => $member['external_id']],
                $member,
            );
        }
    }

    /**
     * The draft itself is recorded, but not its slots: a pick row needs a
     * player and CBS has not run the draft yet, so the 120 empty slots are
     * nothing to write until picks are actually made.
     */
    private function createDraft(League $league): void
    {
        $league->draft()->updateOrCreate(
            ['league_id' => $league->id],
            $this->leagueData['draft'],
        );
    }

    /**
     * Keepers cost no pick in this league, so they are written as roster
     * entries rather than as draft picks.
     */
    private function createKeepers(League $league): void
    {
        $members = LeagueMember::forLeague($league)->get()->keyBy('external_id');

        foreach ($this->leagueData['keepers'] as $keeper) {
            $member = $members->get($keeper['league_member_id']);

            if (!$member instanceof LeagueMember) {
                Log::error('Member not found for CBS keeper', $keeper);

                continue;
            }

            $player = PlayerFacade::find([
                'full_name'   => $keeper['full_name'],
                'position_id' => $this->positionId($keeper['position']),
                'team_id'     => $keeper['team'],
            ], ['source' => static::class]);

            if (!$player instanceof Player) {
                // A keeper the board cannot see reads as a free agent, which
                // is the one mistake a draft room must not make.
                $this->skippedKeepers[] = $keeper;

                Log::error('Player not found for CBS keeper', $keeper);

                continue;
            }

            LeagueMemberRoster::updateOrCreate(
                [
                    'league_member_id' => $member->id,
                    'player_id'        => $player->id,
                    'season'           => $league->season_id,
                    // Week zero is the roster as it stands before any game is
                    // played, which is what a keeper is.
                    'week' => 0,
                ],
                ['lineup_slot_id' => 0],
            );
        }
    }

    private function positionId(?string $position): ?string
    {
        return Arr::get(CBSConstants::POSITION_MAP, $position)?->value;
    }
}
