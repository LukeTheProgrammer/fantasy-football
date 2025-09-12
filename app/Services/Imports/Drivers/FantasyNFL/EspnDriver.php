<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Enums\FantasyPlatformsEnum;
use App\Facades\Espn;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\Player;
use App\Models\User;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\Data\FantasyNFL\LineupSlotCountsData;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;
use App\Services\Espn\Data\FantasyNFL\RosterSettingsData;
use App\Services\Espn\Data\FantasyNFL\SettingsSettingsData;
use App\Services\Espn\EspnConstants;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class EspnDriver extends BaseFantasyNFLDriver
{
    private FantasyNFL $espn;

    private ResourceLeagueData $apiLeague;

    private User $creator;

    private array $draftData = [];

    private array $leagueData = [];

    private array $membersData = [];

    private array $rosterData = [];

    private array $settingsData = [];

    public function setCredentials(array $credentials)
    {
        $this->credentials = CredentialsData::from($credentials);
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

        /** @var DraftSettingsData $draftSettings */
        $draftSettings = $settings->draftSettings;

        /** @var DraftDetailData $draftDetail */
        $draftDetail = $this->apiLeague->draftDetail;

        /** @var Collection $teams */
        $teams = $this->apiLeague->teams;

        $this->leagueData = [
            'created_by_user_id' => $this->creator->id,
            'name'               => $settings->name,
            'year'               => date('Y'),
            'slug'               => 'espn-' . Str::slug($settings->name),
            'description'        => null,
            'platform'           => FantasyPlatformsEnum::ESPN->value,
            'platform_id'        => $this->credentials->leagueId,
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

        $this->draftData = [
            'draft_date'     => $this->getDate($draftSettings->date)?->toDateTimeString(),
            'draft_type'     => $draftSettings->type,
            'is_completed'   => $draftDetail->drafted,
            'auction_budget' => $draftSettings->auctionBudget,
            'time_per_pick'  => $draftSettings->timePerSelection,
            'is_active'      => false,
        ];

        $teams->each(function (ResourceTeamsData $team) use ($members) {
            $this->membersData[] = [
                'external_id'  => $team->id,
                'team_name'    => $team->name,
                'owner_name'   => $this->findOwnerName($team, $members),
                'team_logo'    => $team->logo,
            ];

            $this->rosterData[] = [
                'team_id' => $team->id,
                'players' => $team->roster->entries->map(fn (TeamRosterEntryData $entry) => [
                    'player_id'   => $entry->playerId,
                    'position_id' => $entry->lineupSlotId,
                    'first_name'  => $entry->playerPoolEntry->player->firstName,
                ]),
            ];
        });
    }

    private function createLeague(): League
    {
        $league = League::updateOrCreate(
            [
                'platform' => $this->leagueData['platform'],
                'platform_id' => $this->leagueData['platform_id'],
            ],
            $this->leagueData,
        );

        $league->settings()->updateOrCreate(['league_id' => $league->id], $this->settingsData);

        $league->draft()->updateOrCreate(['league_id' => $league->id], $this->draftData);

        foreach ($this->membersData as $m) {
            $league->members()->updateOrCreate(['external_id' => $m['external_id']], $m);
        }

        $members = LeagueMember::forLeague($league)->get()->keyBy('external_id');

        foreach ($this->rosterData as $roster) {
            $member = $members->get($roster['team_id']);

            if (! $member instanceof LeagueMember) {
                continue;
            }

            $member->rosters()->delete();

            foreach ($roster['players'] as $player) {
                $player = $player['position_id'] === 16
                    ? Player::nameLike($player['first_name'])->first()
                    : Player::where('espn_id', $player['player_id'])->first();

                if ($player instanceof Player) {
                    $member->rosters()->withTrashed()->updateOrCreate([
                        'player_id' => $player->id,
                    ]);
                }
            }
        }

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

    /**
     * Api timestamps are in ms.
     *
     * @param integer|null $timestamp
     *
     * @return Carbon|null
     */
    private function getDate(?int $timestamp): ?Carbon
    {
        return ($timestamp) ? Carbon::parse($timestamp/1000) : null;
    }
}
