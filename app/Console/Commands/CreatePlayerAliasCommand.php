<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreatePlayerAliasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:player-alias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Player Alias.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $positions = Position::all()->pluck('abbreviation', 'id');
        $teams = Team::all()->pluck('abbreviation', 'id');

        $data = [
            'name' => text('Name'),
            'position_id' => select('Position', $positions),
            'team_id' => select('Team', $teams),
            'player_id' => text('Player ID'),
        ];

        $player = Player::findOrFail($data['player_id']);

        $alias = PlayerAlias::updateOrCreate([
            'name' => $data['name'],
            'position_id' => $data['position_id'],
            'team_id' => $data['team_id'],
            'player_id' => $player->id,
        ], $data);

        $this->info('Player Alias created successfully! ' . $alias->id);
    }
}
