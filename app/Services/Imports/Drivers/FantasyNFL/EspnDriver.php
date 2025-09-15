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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class EspnDriver extends BaseFantasyNFLDriver
{
    private FantasyNFL $espn;

    private ResourceLeagueData $apiLeague;

    private User $creator;

    private array $draftData = [];

    private array $draftPickData = [];

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
        $this->mapLeagueData();

        $this->mapSettingsData();

        $this->mapDraftData();

        $this->mapMembersData();
    }

    private function mapLeagueData()
    {
        /** @var SettingsSettingsData $settings */
        $settings = $this->apiLeague->settings;

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
    }

    private function mapSettingsData()
    {
        /** @var ScoringSettingsData $scoring */
        $scoring = $this->apiLeague->settings->scoringSettings;

        /** @var RosterSettingsData $roster */
        $roster = $this->apiLeague->settings->rosterSettings;

        /** @var LineupSlotCountsData $lineup */
        $lineup = $roster->lineupSlotCounts;

        $this->settingsData = [
            'roster_positions' => $this->getRosterPositions($lineup),
            'roster_size'      => $lineup->getPositionCount(),
            'starters_count'   => $lineup->getStartersCount(),
            'bench_count'      => $lineup->getBenchCount(),
            'ir_spots'         => $lineup->IR,
            ...$this->mapScoring($scoring->scoringItems),
        ];
    }

    private function mapDraftData()
    {
        /** @var DraftSettingsData $draftSettings */
        $draftSettings = $this->apiLeague->settings->draftSettings;

        /** @var DraftDetailData $draftDetail */
        $draftDetail = $this->apiLeague->draftDetail;

        $this->draftData = [
            'draft_date'     => $this->getDate($draftSettings->date)?->toDateTimeString(),
            'draft_type'     => $draftSettings->type,
            'is_completed'   => $draftDetail->drafted,
            'auction_budget' => $draftSettings->auctionBudget,
            'time_per_pick'  => $draftSettings->timePerSelection,
            'is_active'      => false,
        ];

        $draftDetail->picks->each(function ($pick) {
            $this->draftPickData[] = [
                'league_member_id'    => $pick->teamId,
                'player_id'           => $pick->playerId,
                'round'               => $pick->roundId,
                'pick_number'         => $pick->roundPickNumber,
                'overall_pick_number' => $pick->overallPickNumber,
                'amount'              => $pick->bidAmount,
                'is_keeper'           => $pick->keeper,
            ];
        });
    }

    private function mapMembersData()
    {
        /** @var Collection $members */
        $members = $this->apiLeague->members;

        $this->apiLeague->teams->each(function (ResourceTeamsData $team) use ($members) {
            $this->membersData[] = [
                'external_id'    => $team->id,
                'team_name'      => $team->name,
                'owner_name'     => $this->findOwnerName($team, $members),
                'team_logo'      => $team->logo,
                'wins'           => $team->record->get('overall.wins', 0),
                'losses'         => $team->record->get('overall.losses', 0),
                'ties'           => $team->record->get('overall.ties', 0),
                'points_for'     => $team->record->get('overall.pointsFor', 0),
                'points_against' => $team->record->get('overall.pointsAgainst', 0),
                'faab_balance'   => 200 - intval($team->transactionCounter->acquisitionBudgetSpent),
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

        $this->createSettings($league);

        $this->createMembers($league);

        $this->createRosters($league);

        $this->createDraft($league);

        return $league;
    }

    private function createSettings(League $league)
    {
        $league->settings()->updateOrCreate(
            ['league_id' => $league->id],
            $this->settingsData,
        );
    }

    private function createMembers(League $league)
    {
        foreach ($this->membersData as $member) {
            $league->members()->updateOrCreate(
                ['external_id' => $member['external_id']],
                $member,
            );
        }
    }

    private function createRosters(League $league)
    {
        $members = LeagueMember::forLeague($league)->get()->keyBy('external_id');

        foreach ($this->rosterData as $roster) {
            $member = $members->get($roster['team_id']);

            if (! $member instanceof LeagueMember) {
                continue;
            }

            $member->rosters()->delete();

            foreach ($roster['players'] as $player) {
                $playerModel = $player['position_id'] === 16
                    ? Player::nameLike($player['first_name'])->first()
                    : Player::where('espn_id', $player['player_id'])->first();

                if ($playerModel instanceof Player) {
                    $member->rosters()->withTrashed()->updateOrCreate(
                        ['player_id' => $playerModel->id],
                        ['deleted_at' => null],
                    );
                }
            }
        }
    }

    private function createDraft(League $league)
    {
        $draft = $league->draft()->updateOrCreate(
            ['league_id' => $league->id],
            $this->draftData,
        );

        $draft->picks()->delete();

        foreach ($this->draftPickData as $pick) {
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
