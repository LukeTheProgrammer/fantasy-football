<?php

namespace App\Console\Commands\Data\Clean;

use App\Models\Player;
use App\Models\PlayerAlias;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class CleanPlayerAliasesCommand extends Command
{
    protected $signature = 'data:clean:player-aliases';

    protected $description = 'Clean player aliases';

    public function handle(): void
    {
        PlayerAlias::query()
            ->get()
            ->each(fn ($alias) => $this->checkAlias($alias));
    }

    protected function checkAlias(PlayerAlias $alias): void
    {
        $this->info(PHP_EOL . $alias->name . ' => ' . $this->playerLabel($alias->player));

        if (confirm('Is this correct?')) {
            return;
        }

        $opts = $this->createOpts($alias);

        $selected = select('Select player ' . $alias->name, $opts);

        if ($selected === '_NULL_') {
            return;
        }

        if ($selected === '_DEL_') {
            $alias->delete();

            return;
        }

        $alias->update([
            'player_ulid' => $selected,
        ]);
    }

    private function playerLabel(Player $player): string
    {
        return '[' . $player->id . '] ' . $player->full_name . ' ' . $player->position_id . ' ' . $player->team_id;
    }

    private function createOpts(PlayerAlias $alias): array
    {
        $nameParts = explode(' ', $alias->name);
        $opts = [
            '_NULL_' => 'None',
            '_DEL_'  => 'Delete',
        ];

        foreach ($nameParts as $part) {
            if (strlen($part) < 2) {
                continue;
            }

            Player::where('last_name', '=', $part)->get()->each(function ($player) use (&$opts) {
                if (!isset($opts[$player->ulid])) {
                    $opts[$player->ulid] = $this->playerLabel($player);
                }
            });

            Player::where('first_name', '=', $part)->get()->each(function ($player) use (&$opts) {
                if (!isset($opts[$player->ulid])) {
                    $opts[$player->ulid] = $this->playerLabel($player);
                }
            });

            Player::nameLike($part)->get()->each(function ($player) use (&$opts) {
                if (!isset($opts[$player->ulid])) {
                    $opts[$player->ulid] = $this->playerLabel($player);
                }
            });

            Player::forTeam($alias->player->team_id)->forPosition($alias->player->position_id)->get()->each(function ($player) use (&$opts) {
                if (!isset($opts[$player->ulid])) {
                    $opts[$player->ulid] = $this->playerLabel($player);
                }
            });
        }

        return $opts;
    }
}
