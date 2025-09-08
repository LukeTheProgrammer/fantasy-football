<?php

namespace App\Console\Commands\Traits;

use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

trait DisambiguatesPlayers
{
    public function disambiguatePlayer(
        string $playerName,
        ?Position $position = null,
        ?Team $team = null,
    ): Player|bool {
        $player = $this->findPlayerByFullName($playerName);

        if ($player instanceof Player) {
            return $this->returnPlayer($player);
        }

        $player = $this->findPlayerByAlias($playerName, $position, $team);

        if ($player instanceof Player) {
            return $this->returnPlayer($player);
        }

        $player = $this->findPlayerByPositionAndTeam($playerName, $position, $team);

        if ($player instanceof Player) {
            $this->savePlayerAlias($player, $playerName);
            return $this->returnPlayer($player);
        }

        return false;
    }

    public function findPlayerByFullName(string $fullName): Player|bool
    {
        $playerQuery = Player::where('full_name', '=', $fullName);

        if ($playerQuery->count() === 1) {
            return $playerQuery->first();
        }

        if ($playerQuery->count() > 1) {
            $players = $playerQuery->get();
            return $this->selectPlayerFromList($fullName, $players);
        }

        return false;
    }

    public function findPlayerByAlias(string $alias, ?Position $position = null, ?Team $team = null): Player|bool
    {
        $aliasQuery = PlayerAlias::where('name', '=', $alias)
            ->when($position !== null, fn($q) => $q->where('position_id', '=', $position->id))
            ->when($team !== null, fn($q) => $q->where('team_id', '=', $team->id));

        $queryCount = $aliasQuery->count();

        if ($queryCount === 0) {
            return false;
        }

        if ($queryCount === 1) {
            return $aliasQuery->first()->player;
        }

        if ($queryCount > 1) {
            $aliases = $aliasQuery->get();
            return $this->selectPlayerAliasFromList($alias, $aliases);
        }

        return false;
    }

    public function findPlayerByPositionAndTeam(string $playerName, ?Position $position = null, ?Team $team = null): Player|bool
    {
        if ($position === null || $team === null) {
            return false;
        }

        $playerQuery = Player::query()
            ->when($position !== null, fn($q) => $q->where('position_id', '=', $position->id))
            ->when($team !== null, fn($q) => $q->where('team_id', '=', $team->id));

        $queryCount = $playerQuery->count();

        if ($queryCount === 1) {
            return $playerQuery->first();
        }

        if ($queryCount > 1) {
            $players = $playerQuery->get();
            return $this->selectPlayerFromList($playerName, $players);
        }

        return false;
    }

    public function selectPlayerFromList(string $playerName, Collection $players): Player|bool
    {
        $options = $players->mapWithKeys(function ($player) {
            $label = implode (' ', [
                $player->full_name,
                $player->position->abbreviation,
                $player->team->abbreviation,
            ]);

            return [$player->id => $label];
        });

        $options['none'] = 'None';

        $playerId = select(
            label: 'Which player matches ' . $playerName,
            options: $options->toArray(),
        );

        if ($playerId === 'none') {
            return false;
        }

        return $players->where('id', '=', $playerId)->first();
    }

    public function selectPlayerAliasFromList(string $playerName, Collection $aliases): Player|bool
    {
        $options = $aliases->map(function ($alias) {
            $label = implode (' ', [
                $alias->name,
                $alias->position->abbreviation,
                $alias->team->abbreviation,
            ]);

            return [$alias->player_id => $label];
        });

        $options['none'] = 'None';

        $playerId = select(
            label: 'Which player matches ' . $playerName,
            options: $options->toArray(),
        );

        if ($playerId === 'none') {
            return false;
        }

        return Player::findOrFail($playerId);
    }

    public function savePlayerAlias(Player $player, string $alias): void
    {
        if (confirm('Player match found! Would you like to save an Alias?')) {
            PlayerAlias::create([
                'player_id'   => $player->id,
                'team_id'     => $player->team_id,
                'position_id' => $player->position_id,
                'name'        => $alias,
            ]);
        }
    }

    public function returnPlayer(Player $player): Player
    {
        return $player->loadMissing(['position', 'team']);
    }
}
