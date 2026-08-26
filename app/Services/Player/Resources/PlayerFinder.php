<?php

namespace App\Services\Player\Resources;

use App\Enums\NFLTeams;
use App\Facades\Action;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\PlayerMissing;
use App\Services\Player\Helpers\NormalizedName;
use Illuminate\Support\Arr;

/**
 * Resolves a player from whatever identifying data a source gives us.
 *
 * Every import path resolves players through here, so a name that one source
 * spells differently is learned once and understood everywhere after that.
 */
class PlayerFinder
{
    /**
     * Source id columns on the players table, keyed by the option that names one.
     *
     * @var array<string, string>
     */
    public const ID_COLUMNS = [
        'gsis_id' => 'gsis_id',
        'fp_id'   => 'fp_id',
        'espn_id' => 'espn_id',
        'pfr_id'  => 'pfr_id',
    ];

    private ?Player $player = null;

    private bool $recordMissing = true;

    private ?string $source = null;

    public function __construct(private array $data, private array $opts = [])
    {
        $this->recordMissing = Arr::get($opts, 'record_missing', true);
        $this->source = Arr::get($opts, 'source');
    }

    /**
     * The resolved player, or null when nothing matched.
     */
    public function player(): ?Player
    {
        $this->searchBySourceId();
        $this->searchByName();
        $this->searchByNormalizedName();
        $this->searchByAlias();
        $this->searchByNamePositionAndTeam();

        if (!$this->player instanceof Player) {
            $this->saveMissingPlayer();

            return null;
        }

        return $this->player;
    }

    /**
     * The source's own id is the only identifier that survives a name change,
     * so it is always tried first.
     */
    private function searchBySourceId(): void
    {
        if ($this->player instanceof Player) {
            return;
        }

        foreach (self::ID_COLUMNS as $key => $column) {
            $id = Arr::get($this->data, $key);

            if (empty($id)) {
                continue;
            }

            $player = Player::where($column, $id)->first();

            if ($player instanceof Player) {
                $this->player = $player;

                return;
            }
        }
    }

    private function searchByName(): void
    {
        if ($this->player instanceof Player || empty($this->name())) {
            return;
        }

        $query = Player::where('full_name', '=', $this->name());

        if ($query->count() === 1) {
            $this->player = $query->first();
        }
    }

    /**
     * Sources disagree about suffixes and punctuation more often than they
     * disagree about who a player is, so the reduced forms are compared before
     * the search moves on to guessing.
     */
    private function searchByNormalizedName(): void
    {
        if ($this->player instanceof Player || empty($this->normalizedName())) {
            return;
        }

        $query = Player::where('normalized_name', '=', $this->normalizedName());

        if ($query->count() === 1) {
            $this->player = $query->first();
        }
    }

    private function searchByAlias(): void
    {
        if ($this->player instanceof Player || empty($this->name())) {
            return;
        }

        $query = PlayerAlias::where('name', '=', $this->name());

        if ($query->count() === 1) {
            $this->player = $query->first()->player;

            return;
        }

        $normalized = PlayerAlias::forNormalizedName($this->normalizedName());

        if ($this->normalizedName() && $normalized->count() === 1) {
            $this->player = $normalized->first()->player;
        }
    }

    /**
     * A duplicated name is only ambiguous until position and team narrow it.
     */
    private function searchByNamePositionAndTeam(): void
    {
        if ($this->player instanceof Player || empty($this->normalizedName())) {
            return;
        }

        $position = Arr::get($this->data, 'position_id');
        $team = $this->teamId();

        if (empty($position) || empty($team)) {
            return;
        }

        $query = Player::where('normalized_name', '=', $this->normalizedName())
            ->where('position_id', '=', $position)
            ->where('team_id', '=', $team);

        if ($query->count() === 1) {
            $this->player = $query->first();
        }
    }

    /**
     * Log an unresolved player so the gap is visible rather than silent.
     *
     * A source id with no name is still worth recording — a draft pick arrives
     * as nothing but an id, and an unrecorded miss there is a lost pick.
     */
    private function saveMissingPlayer(): void
    {
        [$idKey, $idValue] = $this->sourceId();

        if (!$this->recordMissing || (empty($this->name()) && empty($idValue))) {
            return;
        }

        Action::model(PlayerMissing::class)->upsert($this->data, $this->source ?? static::class, [
            'unique_id_key'   => $idKey,
            'unique_id_value' => $idValue,
            'name'            => $this->name(),
            'position_id'     => Arr::get($this->data, 'position_id'),
            'team_id'         => $this->teamId(),
        ]);
    }

    /**
     * The first source id present in the data, as a key and value pair.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function sourceId(): array
    {
        foreach (array_keys(self::ID_COLUMNS) as $key) {
            $id = Arr::get($this->data, $key);

            if (!empty($id)) {
                return [$key, (string) $id];
            }
        }

        return [null, null];
    }

    private function name(): ?string
    {
        return Arr::get($this->data, 'full_name');
    }

    private function normalizedName(): ?string
    {
        return NormalizedName::of($this->name());
    }

    /**
     * Team abbreviations differ per source, so they are normalised on the way in.
     */
    private function teamId(): ?string
    {
        return NFLTeams::fromAbbreviation(Arr::get($this->data, 'team_id'))?->value;
    }
}
