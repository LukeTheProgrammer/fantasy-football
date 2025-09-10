<?php

namespace App\Services\Imports\Drivers\Leagues;

use App\Facades\Espn;
use App\Models\League;
use App\Models\User;
use App\Services\Espn\Data\FantasyNFL\FantasyNFLCredentialsData;
use App\Services\Espn\Data\FantasyNFL\LineupSlotCountsData;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Data\FantasyNFL\ResourceSettingsData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;
use App\Services\Espn\Data\FantasyNFL\RosterSettingsData;
use App\Services\Espn\Data\FantasyNFL\SettingsSettingsData;
use App\Services\Espn\EspnConstants;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EspnDriver extends BaseLeagueDriver
{
    private FantasyNFL $espn;

    private ResourceLeagueData $apiLeague;

    // private ResourceSettingsData $apiSettings;

    // private ResourceTeamsData $apiTeams;

    private User $creator;

    private array $leagueData = [];

    private array $settingsData = [];

    private array $membersData = [];

    public function setCredentials(array $credentials)
    {
        $this->credentials = FantasyNFLCredentialsData::from($credentials);
        $this->espn = Espn::fantasyNFL($this->credentials);
    }

    public function setCreator(User $user)
    {
        $this->creator = $user;
    }

    public function import(): League
    {
        $this->loadData();

        $this->mapData();

        return $this->createLeague();
    }

    //

    private function loadData()
    {
        $this->apiLeague = $this->espn->getLeague();
        // $this->apiSettings = $this->espn->getSettings();
        // $this->apiTeams = $this->espn->getTeams();
    }

    private function mapData()
    {
        /** @var Collection $members */
        $members = $this->apiLeague->members;

        /** @var SettingsSettingsData $settings */
        $settings = $this->apiLeague->settings;

        /** @var RosterSettingsData $roster */
        $roster = $settings->rosterSettings;

        /** @var LineupSlotCountsData $lineup */
        $lineup = $roster->lineupSlotCounts;

        /** @var ScoringSettingsData $scoring */
        $scoring = $settings->scoringSettings;

        $this->leagueData = [
            'created_by_user_id' => $this->creator->id,
            'name'               => $settings->name,
            'year'               => date('Y'),
            'slug'               => 'espn-' . Str::slug($settings->name),
            'description'        => null,
            'team_count'         => $settings->size,
            'is_public'          => $settings->isPublic,
            'join_code'          => Str::upper(Str::random(8)),
            'is_active'          => true,
            'credentials'        => $this->credentials->toArray(),
        ];

        $this->settingsData = [
            'roster_positions' => $this->getRosterPositions($lineup),
            'roster_size'      => $lineup->getPositionCount(),
            'starters_count'   => $lineup->getStartersCount(),
            'bench_count'      => $lineup->getBenchCount(),
            'ir_spots'         => $lineup->IR,
            ...$this->mapScoring($scoring->scoringItems),
        ];

        foreach ($this->apiLeague->teams as $team) {
            $this->membersData[] = [
                'external_id'  => $team->id,
                'team_name'    => $team->name,
                'owner_name'   => $this->findOwnerName($team, $members),
                'team_logo'    => $team->logo,
            ];
        }
    }

    private function createLeague(): League
    {
        $league = League::create($this->leagueData);

        $league->settings()->create($this->settingsData);

        $league->members()->createMany($this->membersData);

        return $league;
    }

    private function getRosterPositions(LineupSlotCountsData $lineup): array
    {
        $positions = [];

        foreach ($lineup->toArray() as $slot => $count) {
            if ($count > 0) {
                $positions = array_merge($positions, array_fill(0, $count, $slot));
            }
        }

        return $positions;
    }

    private function mapScoring(Collection $scoring): array
    {
        $mapped = [];

        foreach (EspnConstants::SCORING_MAP as $espnKey => $modelKey) {
            $value = $scoring->firstWhere('label', $espnKey);

            if ($value) {
                $mapped[$modelKey] = $value->value;
            }
        }

        return $mapped;
    }

    private function findOwnerName(ResourceTeamsData $team, Collection $members): string
    {
        $member = $members->firstWhere('id', $team->primaryOwner);

        return ($member)
            ? $member->firstName . ' ' . $member->lastName
            : $team->name . ' Owner';
    }
}
