<?php

namespace App\Services\Espn\Formatters;

use App\Enums\FantasyPlatforms;
use App\Services\Espn\Data\FantasyNFL\LineupSlotCountsData;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;
use App\Services\Espn\Data\FantasyNFL\RosterSettingsData;
use App\Services\Espn\Data\FantasyNFL\ScheduleData;
use App\Services\Espn\Data\FantasyNFL\SettingsSettingsData;
use App\Services\Espn\EspnConstants;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FantasyLeagueFormatter
{
    private array $data = [
        'draft'      => [],
        'draftPicks' => [],
        'league'     => [],
        'members'    => [],
        'roster'     => [],
        'schedules'  => [],
        'settings'   => [],
    ];

    public function __construct(protected ResourceLeagueData $league)
    {
        //
    }

    public static function from(array|ResourceLeagueData $data)
    {
        if (! $data instanceof ResourceLeagueData) {
            $data = ResourceLeagueData::from($data);
        }

        $formatter = new FantasyLeagueFormatter($data);

        return $formatter->format();
    }

    public function format()
    {
        $this->formatData();

        return $this->data;
    }

    public function formatData()
    {
        $this->formatLeagueData();

        $this->formatSettingsData();

        $this->formatDraftData();

        $this->formatMembersData();

        $this->formatScheduleData();
    }

    private function formatLeagueData()
    {
        /** @var SettingsSettingsData $settings */
        $settings = $this->league->settings;

        $this->data['league'] = [
            'name'        => $settings->name,
            'season'      => date('Y'),
            'slug'        => 'espn-' . Str::slug($settings->name),
            'description' => null,
            'platform'    => FantasyPlatforms::ESPN->value,
            'team_count'  => $settings->size,
            'is_public'   => $settings->isPublic,
            'join_code'   => Str::upper(Str::random(8)),
            'is_active'   => true,
        ];
    }

    private function formatSettingsData()
    {
        /** @var ScoringSettingsData $scoring */
        $scoring = $this->league->settings->scoringSettings;

        /** @var RosterSettingsData $roster */
        $roster = $this->league->settings->rosterSettings;

        /** @var LineupSlotCountsData $lineup */
        $lineup = $roster->lineupSlotCounts;

        $this->data['settings'] = [
            'roster_positions' => $this->getRosterPositions($lineup),
            'roster_size'      => $lineup->getPositionCount(),
            'starters_count'   => $lineup->getStartersCount(),
            'bench_count'      => $lineup->getBenchCount(),
            'ir_spots'         => $lineup->IR,
            ...$this->mapScoringSettings($scoring->scoringItems),
        ];

        $recPts = Arr::get($this->data, 'settings.reception_points', 0);
        $ppr = ($recPts >= 1)
            ? 'ppr'
            : (($recPts >= 0) ? 'half-ppr' : 'standard');

        $this->data['settings']['ppr'] = $ppr;
        $this->data['settings']['two_qb'] = $this->isTwoQb();
    }

    private function formatDraftData()
    {
        /** @var DraftSettingsData $draftSettings */
        $draftSettings = $this->league->settings->draftSettings;

        /** @var DraftDetailData $draftDetail */
        $draftDetail = $this->league->draftDetail;

        $this->data['draft'] = [
            'draft_date'     => $this->getDate($draftSettings->date)?->toDateTimeString(),
            'draft_type'     => $draftSettings->type,
            'is_completed'   => $draftDetail->drafted,
            'auction_budget' => $draftSettings->auctionBudget,
            'time_per_pick'  => $draftSettings->timePerSelection,
            'is_active'      => false,
        ];

        $draftDetail->picks->each(function ($pick) {
            $this->data['draftPicks'][] = [
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

    private function formatMembersData()
    {
        /** @var Collection $members */
        $members = $this->league->members;

        $this->league->teams->each(function (ResourceTeamsData $team) use ($members) {

            /** @var ?TeamRecordData $record */
            $record = $team->record->get('overall', []);

            $this->data['members'][] = [
                'external_id'    => $team->id,
                'team_name'      => $team->name,
                'owner_name'     => $this->findOwnerName($team, $members),
                'team_logo'      => $team->logo,
                'wins'           => $record?->wins,
                'losses'         => $record?->losses,
                'ties'           => $record?->ties,
                'points_for'     => $record?->pointsFor,
                'points_against' => $record?->pointsAgainst,
                'faab_balance'   => 200 - intval($team->transactionCounter->acquisitionBudgetSpent),
            ];
        });
    }

    private function formatScheduleData()
    {
        $this->league->schedule->each(function (ScheduleData $schedule) {
            $this->data['schedules'][] = [
                'home_member_id' => $schedule->home->teamId,
                'away_member_id' => $schedule->away->teamId,
                'season' => 2025,
                'week' => $schedule->matchupPeriodId,
                'home_score' => $schedule->home->totalPoints,
                'away_score' => $schedule->away->totalPoints,
            ];
        });
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

    private function mapScoringSettings(Collection $scoring): array
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

    private function isTwoQb(): bool
    {
        $roster = collect(Arr::get($this->data, 'settings.roster_positions', []));

        return $roster->filter(fn ($p) => $p === 'QB')->count() > 1;
    }
}
