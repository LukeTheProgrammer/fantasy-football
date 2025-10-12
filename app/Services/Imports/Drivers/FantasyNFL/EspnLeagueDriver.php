<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Facades\Data;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMatchup;
use App\Models\Player;
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

    public function __construct(private array $metaData = [])
    {
        $this->creator = User::findOrFail(Arr::get($metaData, 'created_by_user_id'));

        $this->credentials = CredentialsData::from([
            'leagueId' => Arr::get($metaData, 'league_id'),
            's2' => Arr::get($metaData, 's2'),
            'swid' => Arr::get($metaData, 'swid'),
        ]);

        if (! $this->credentials instanceof CredentialsData) {
            throw new InvalidArgumentException('Invalid credentials');
        }
    }

    public function import(): League
    {
        $this->loadData();

        $league = $this->createLeague();

        Data::espn()->getFantasyLeagueRosters($league, $league->season);

        return $league;
    }

    //

    private function loadData()
    {
        $this->leagueData = Data::espn()->getFantasyLeague(
            credentials: $this->credentials
        );
    }

    private function createLeague(): League
    {
        $leagueData = $this->leagueData['league'];

        $leagueData['created_by_user_id'] = $this->creator->id;
        $leagueData['platform_id'] = $this->credentials->leagueId;
        $leagueData['credentials'] = $this->credentials;

        $league = League::updateOrCreate(
            [
                'platform' => $leagueData['platform'],
                'platform_id' => $leagueData['platform_id'],
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

            if (! $member instanceof LeagueMember) {
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

            if (! $member instanceof LeagueMember) {
                Log::error('Member not found for draft pick', $pick);
                continue;
            }

            $player = Player::espnId($pick['player_id'])->first();

            if (! $player instanceof Player) {
                Log::error('Player not found for draft pick', $pick);
                continue;
            }

            $find = [
                'draft_id' => $draft->id,
                'league_member_id' => $member->id,
                'player_id' => $player->id,
            ];

            $update = [
                'round' => $pick['round'],
                'pick_number' => $pick['pick_number'],
                'overall_pick_number' => $pick['overall_pick_number'],
                'amount' => $pick['amount'],
                'is_keeper' => $pick['is_keeper'],
            ];

            DraftPick::updateOrCreate($find, $update);
        }
    }

    private function createMatchups(League $league)
    {
        foreach ($this->leagueData['schedules'] as $matchup) {
            $homeMember = LeagueMember::forLeague($league)->forExtId($matchup['home_member_id'])->first();
            $awayMember = LeagueMember::forLeague($league)->forExtId($matchup['away_member_id'])->first();

            if (! $homeMember instanceof LeagueMember) {
                Log::error('Member not found for home team id', $matchup);
                continue;
            }

            if (! $awayMember instanceof LeagueMember) {
                Log::error('Member not found for away team id', $matchup);
                continue;
            }

            $find = [
                'league_id' => $league->id,
                'season' => $matchup['season'],
                'week' => $matchup['week'],
                'home_member_id' => $homeMember->id,
                'away_member_id' => $awayMember->id,
            ];

            $update = array_filter([
                'home_score' => Arr::get($matchup, 'home_score'),
                'away_score' => Arr::get($matchup, 'away_score'),
                'home_projected_score' => Arr::get($matchup, 'home_projected_score'),
                'away_projected_score' => Arr::get($matchup, 'away_projected_score'),
            ]);

            LeagueMatchup::updateOrCreate($find, $update);
        }
    }
}
