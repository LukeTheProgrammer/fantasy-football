<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Enums\Datum;
use App\Facades\Data;
use App\Models\League;
use App\Models\LeagueMember;
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

        return $this->createLeague();
    }

    //

    private function loadData()
    {
        $this->leagueData = Data::forcePull(false)
            ->dataFormat(Datum::FORMAT_FORMATTED)
            ->espn()
            ->getFantasyLeague(
                credentials: $this->credentials
            );
    }

    private function createLeague(): League
    {
        $leagueData = $this->leagueData['league'];

        $leagueData['created_by_user_id'] = $this->creator->id;
        $leagueData['platform_id'] = Arr::get($this->credentials, 'leagueId');
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
            $member = LeagueMember::forExtId($pick['league_member_id'])->first();

            if (! $member instanceof LeagueMember) {
                Log::error('Member not found for draft pick', $pick);
                continue;
            }

            $player = Player::espnId($pick['player_id'])->first();

            if (! $player instanceof Player) {
                Log::error('Player not found for draft pick', $pick);
                continue;
            }

            $pick['league_member_id'] = $member->id;
            $pick['player_id'] = $player->id;

            $draft->picks()->updateOrCreate(
                ['league_member_id' => $member->id, 'player_id' => $player->id],
                $pick,
            );
        }
    }

    private function createMatchups(League $league)
    {
        foreach ($this->leagueData['schedules'] as $matchup) {
            $homeMember = LeagueMember::forExtId($matchup['home_member_id'])->first();
            $awayMember = LeagueMember::forExtId($matchup['away_member_id'])->first();

            if (! $homeMember instanceof LeagueMember) {
                Log::error('Member not found for home team id', $matchup);
                continue;
            }

            if (! $awayMember instanceof LeagueMember) {
                Log::error('Member not found for away team id', $matchup);
                continue;
            }

            $league->matchups()->updateOrCreate(
                ['home_member_id' => $homeMember->id, 'away_member_id' => $awayMember->id],
                $matchup,
            );
        }
    }
}
