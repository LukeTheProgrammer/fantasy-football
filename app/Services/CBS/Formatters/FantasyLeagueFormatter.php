<?php

namespace App\Services\CBS\Formatters;

use App\Enums\FantasyPlatforms;
use App\Services\CBS\CBSConstants;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Turns the several CBS reads into the one shape the league importer writes.
 *
 * CBS has no single league endpoint, so the payloads arrive separately and are
 * assembled here rather than by the driver.
 */
class FantasyLeagueFormatter
{
    private array $data = [
        'draft'      => [],
        'draftPicks' => [],
        'keepers'    => [],
        'league'     => [],
        'members'    => [],
        'settings'   => [],
    ];

    public function __construct(
        private array $details,
        private array $owners,
        private array $rules,
        private array $scoring,
        private array $draftConfig,
        private array $draftOrder,
        private array $keepers,
        private int $season,
    ) {
        //
    }

    public static function from(array $payloads, int $season): array
    {
        $formatter = new self(
            details: Arr::get($payloads, 'details', []),
            owners: Arr::get($payloads, 'owners', []),
            rules: Arr::get($payloads, 'rules', []),
            scoring: Arr::get($payloads, 'scoring', []),
            draftConfig: Arr::get($payloads, 'draftConfig', []),
            draftOrder: Arr::get($payloads, 'draftOrder', []),
            keepers: Arr::get($payloads, 'keepers', []),
            season: $season,
        );

        return $formatter->format();
    }

    public function format(): array
    {
        $this->formatLeagueData();

        $this->formatSettingsData();

        $this->formatMembersData();

        $this->formatDraftData();

        $this->formatKeepersData();

        return $this->data;
    }

    private function details(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->details, 'league_details.' . $key, $default);
    }

    private function formatLeagueData(): void
    {
        $name = $this->details('name', 'CBS League');

        $this->data['league'] = [
            'name'        => $name,
            'season'      => $this->season,
            'slug'        => 'cbs-' . Str::slug($name) . '-' . $this->season,
            'description' => null,
            'platform'    => FantasyPlatforms::CBS->value,
            'team_count'  => (int) $this->details('num_teams', 0),
            // CBS leagues are invite only and the app is single user, so a
            // league read through a personal token is never public.
            'is_public' => false,
            'join_code' => Str::upper(Str::random(8)),
            'is_active' => true,
        ];
    }

    private function formatSettingsData(): void
    {
        $positions = Arr::get($this->rules, 'rules.roster.positions', []);

        $starters = $this->startersCount($positions);
        $rosterSize = $this->rosterSize();

        $this->data['settings'] = [
            'roster_positions' => $this->rosterPositions($positions),
            'roster_size'      => $rosterSize,
            'starters_count'   => $starters,
            'bench_count'      => max($rosterSize - $starters, 0),
            // CBS carries injured slots in the roster status rows, not as a
            // lineup slot of their own.
            'ir_spots'               => (int) $this->statusMax('Injured Players'),
            'playoff_team_count'     => null,
            'regular_season_weeks'   => (int) $this->details('regular_season_periods', 0) ?: null,
            'playoff_matchup_length' => 1,
            'playoff_reseed'         => false,
            ...$this->mapScoringSettings(),
        ];

        $reception = Arr::get($this->data, 'settings.reception_points', 0);

        // CBS reports is_ppr for any reception scoring at all, so the rate is
        // what separates full from half.
        $this->data['settings']['ppr'] = match (true) {
            $reception >= 1 => 'ppr',
            $reception > 0  => 'half-ppr',
            default         => 'standard',
        };

        $this->data['settings']['two_qb'] = $this->isTwoQb($positions);
    }

    private function formatMembersData(): void
    {
        foreach (Arr::get($this->owners, 'owners', []) as $owner) {
            $team = Arr::get($owner, 'team', []);

            $this->data['members'][] = [
                'external_id' => Arr::get($team, 'id'),
                'team_name'   => Arr::get($team, 'name'),
                'owner_name'  => Arr::get($owner, 'name'),
                'team_logo'   => Arr::get($team, 'logo'),
                'is_admin'    => (bool) Arr::get($owner, 'commissioner', 0),
            ];
        }
    }

    private function formatDraftData(): void
    {
        $draft = Arr::get($this->draftConfig, 'draft', []);

        $this->data['draft'] = [
            'draft_date' => $this->draftDate($draft),
            // CBS's "type" is how the draft is run (live, offline); the order
            // type is what says whether the board reverses each round.
            'draft_type'   => Arr::get($draft, 'order_type') === 'snaking' ? 'snake' : 'linear',
            'is_completed' => $this->details('draft_state') === 'complete',
            // A pick draft has no budget to spend.
            'auction_budget' => null,
            'is_active'      => false,
            'rounds'         => (int) Arr::get($draft, 'rounds', 0),
        ];

        // CBS does not publish a clock for a live draft, so the column's own
        // default stands rather than a guess written over it.
        $timePerPick = (int) Arr::get($draft, 'time_per_pick', 0);

        if ($timePerPick > 0) {
            $this->data['draft']['time_per_pick'] = $timePerPick;
        }

        // CBS publishes the order as one row per slot for the whole draft.
        // A commissioner can set that order by hand, so it is carried as it
        // was given rather than derived from a seed order.
        $this->data['draftPicks'] = [];

        $teams = max((int) $this->details('num_teams', 0), 1);

        foreach (Arr::get($this->draftOrder, 'draft_order.picks', []) as $pick) {
            // CBS numbers a pick across the whole draft; the app also wants
            // where it falls inside its round.
            $overall = (int) Arr::get($pick, 'number');

            $this->data['draftPicks'][] = [
                'league_member_id'    => Arr::get($pick, 'team.id'),
                'round'               => (int) Arr::get($pick, 'round'),
                'pick_number'         => (($overall - 1) % $teams) + 1,
                'overall_pick_number' => $overall,
            ];
        }

        $this->data['draft']['draft_order'] = array_column($this->data['draftPicks'], 'league_member_id');
    }

    /**
     * Keepers are already on rosters and cost no pick, so they are kept apart
     * from the draft and written against the member instead.
     */
    private function formatKeepersData(): void
    {
        foreach (Arr::get($this->keepers, 'keepers', []) as $keeper) {
            $this->data['keepers'][] = [
                'league_member_id' => Arr::get($keeper, 'team_id'),
                'player_id'        => Arr::get($keeper, 'player_id'),
                'full_name'        => Arr::get($keeper, 'name'),
                'position'         => Arr::get($keeper, 'pri_pos'),
                'team'             => Arr::get($keeper, 'pro_team'),
            ];
        }
    }

    /**
     * Adds the keepers of one team, which CBS serves from the roster endpoint
     * rather than the keeper list when the commissioner set them by hand.
     */
    public static function keepersFromRoster(array $roster): array
    {
        $keepers = [];

        foreach (Arr::get($roster, 'rosters.teams', []) as $team) {
            foreach (Arr::get($team, 'players', []) as $player) {
                $keepers[] = [
                    'league_member_id' => Arr::get($team, 'id'),
                    'player_id'        => Arr::get($player, 'id'),
                    'full_name'        => Arr::get($player, 'fullname'),
                    'position'         => Arr::get($player, 'position'),
                    'team'             => Arr::get($player, 'pro_team'),
                ];
            }
        }

        return $keepers;
    }

    /**
     * CBS states a lineup as a min and a max per position rather than as a
     * list of slots, so the minimums are the named slots and whatever the
     * active count has left over is the flex the maximums are there to allow.
     */
    private function rosterPositions(array $positions): array
    {
        $slots = [];

        foreach ($positions as $position) {
            $abbr = Arr::get($position, 'abbr');
            $count = (int) Arr::get($position, 'min_active', 0);

            $slots = array_merge($slots, array_fill(0, max($count, 0), $abbr));
        }

        $flex = $this->startersCount($positions) - count($slots);

        $slots = array_merge($slots, array_fill(0, max($flex, 0), 'RB_WR_TE'));

        // The bench is part of the roster shape even though CBS states it as a
        // total rather than as slots, and a board that leaves it out has
        // nowhere to put the players who are not starting.
        $bench = $this->rosterSize() - count($slots);

        return array_merge($slots, array_fill(0, max($bench, 0), 'BE'));
    }

    private function startersCount(array $positions): int
    {
        $active = $this->statusMax('Active Players');

        if ($active !== null) {
            return (int) $active;
        }

        return array_sum(array_map(fn ($p) => (int) Arr::get($p, 'min_active', 0), $positions));
    }

    private function rosterSize(): int
    {
        return (int) ($this->statusMax('Total Players') ?? 0);
    }

    private function statusMax(string $description): ?string
    {
        $status = collect(Arr::get($this->rules, 'rules.roster.statuses', []))
            ->firstWhere('description', $description);

        return $status ? Arr::get($status, 'max') : null;
    }

    /**
     * CBS has no superflex flag, so a lineup that starts more than one
     * quarterback is what says the league is one.
     */
    private function isTwoQb(array $positions): bool
    {
        $qb = collect($positions)->firstWhere('abbr', 'QB');

        return $qb ? (int) Arr::get($qb, 'max_active', 1) > 1 : false;
    }

    private function mapScoringSettings(): array
    {
        $categories = collect(Arr::get($this->scoring, 'scoring_rules.categories', []));

        $mapped = [];

        foreach (CBSConstants::SCORING_MAP as $cbsKey => $column) {
            $category = $categories->firstWhere('name', $cbsKey);

            if (!$category) {
                continue;
            }

            $mapped[$column] = $this->categoryPoints($category);
        }

        return $mapped;
    }

    /**
     * A flat category carries its points; a per-yard one carries a range that
     * prices so many points per so many yards.
     */
    private function categoryPoints(array $category): float
    {
        $points = Arr::get($category, 'points');

        if ($points !== null && $points !== '') {
            return (float) $points;
        }

        $range = Arr::first(Arr::get($category, 'ranges', []));

        if (!$range) {
            return 0.0;
        }

        $per = (float) Arr::get($range, 'per', 1);

        return $per > 0
            ? (float) Arr::get($range, 'points', 0) / $per
            : (float) Arr::get($range, 'points', 0);
    }

    /**
     * CBS dates the draft as YYYYMMDD with the time as a separate HHMM.
     */
    private function draftDate(array $draft): ?string
    {
        $date = Arr::get($draft, 'date');

        if (empty($date)) {
            return null;
        }

        $time = str_pad((string) Arr::get($draft, 'time', '0000'), 4, '0', STR_PAD_LEFT);

        return Carbon::createFromFormat('YmdHi', $date . $time)->toDateTimeString();
    }
}
