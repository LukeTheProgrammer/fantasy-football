<?php

namespace App\Console\Commands\Traits;

use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

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

        $player = $this->findPlayerByAlias($playerName);

        if ($player instanceof Player) {
            return $this->returnPlayer($player);
        }

        $player = $this->findPlayerByPositionAndTeam($playerName, $position, $team);

        if ($player instanceof Player) {
            return $this->returnPlayer($player);
        }

        return false;
    }

    public function findPlayerByFullName(string $fullName): Player|bool
    {
        $playerQuery = Player::where('full_name', '=', $fullName)->with(['position', 'team']);

        if ($playerQuery->count() === 1) {
            return $playerQuery->first();
        }

        return false;
    }

    public function findPlayerByAlias(string $alias, ?Position $position = null, ?Team $team = null): Player|bool
    {
        $aliasQuery = PlayerAlias::where('name', '=', $alias)
            ->when($position !== null, fn($query) => $query->where('position_id', '=', $position->id))
            ->when($team !== null, fn($query) => $query->where('team_id', '=', $team->id));

        $queryCount = $aliasQuery->count();

        if ($queryCount === 0) {
            return false;
        }

        if ($queryCount === 1) {
            return $aliasQuery->first()->player;
        }

        if ($queryCount > 1) {
            $aliases = $aliasQuery->get();
            $player = $this->selectPlayerAliasFromList($alias, $aliases);
            return $player;
        }

        return false;
    }

    public function findPlayerByPositionAndTeam(string $playerName, ?Position $position = null, ?Team $team = null): Player|bool
    {
        if ($position === null || $team === null) {
            return false;
        }

        $playerQuery = Player::where('full_name', '=', $playerName)
            ->when($position !== null, fn($query) => $query->where('position_id', '=', $position->id))
            ->when($team !== null, fn($query) => $query->where('team_id', '=', $team->id));

        $queryCount = $playerQuery->count();

        if ($queryCount === 0) {
            return false;
        }

        if ($queryCount === 1) {
            $player = $playerQuery->first();

            if (confirm('Player match found! Would you like to save an Alias?')) {
                $this->savePlayerAlias($player, $playerName, $team->abbreviation, $position->abbreviation);
            }

            return $player;
        }

        if ($queryCount > 1) {
            $players = $playerQuery->get();
            $player = $this->selectPlayerFromList($playerName, $players);
            dd($player);
        }

        return false;
    }

    public function selectPlayerFromList(string $playerName, Collection $players): Player
    {
        $options = $players->map(function ($player) {
            return [
                $player->full_name . ' (' . $player->position->name . ')',
                $player->id,
            ];
        });

        $player = select(
            label: 'Which player matches ' . $playerName,
            options: $options->toArray(),
        );

        return $players->where('id', '=', $player)->first();
    }

    public function selectPlayerAliasFromList(string $playerName, Collection $aliases): Player
    {
        $options = $aliases->map(function ($alias) {
            return [
                $alias->name . ' (' . $alias->position . ' ' . $alias->team . ')',
                $alias->player_id,
            ];
        });

        $playerId = select(
            label: 'Which player matches ' . $playerName,
            options: $options->toArray(),
        );

        return Player::findOrFail($playerId);
    }

    public function savePlayerAlias(Player $player, string $alias, ?string $team = null, ?string $position = null): void
    {
        if (confirm('Player match found! Would you like to save an Alias?')) {
            PlayerAlias::create([
                'player_id' => $player->id,
                'alias' => $alias,
                'team' => $team,
                'position' => $position,
            ]);
        }
    }

    public function returnPlayer(Player $player): Player
    {
        return $player->loadMissing(['position', 'team']);
    }
}
