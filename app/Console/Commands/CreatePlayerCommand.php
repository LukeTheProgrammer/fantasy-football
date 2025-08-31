<?php

namespace App\Console\Commands;

use App\Models\Player;
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

class CreatePlayerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:player';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Player.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $positions = Position::all()->pluck('abbreviation', 'id');
        $teams = Team::all()->pluck('abbreviation', 'id');

        $data = [
            'position_id' => select('Position', $positions),
            'team_id' => select('Team', $teams),
            'first_name' => text('First Name'),
            'last_name' => text('Last Name'),
            'jersey_number' => text('Jersey Number', ''),
        ];

        $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];

        $player = Player::updateOrCreate([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'position_id' => $data['position_id'],
            'team_id' => $data['team_id'],
        ], $data);

        $this->info('Player created successfully! ' . $player->id);
    }
}
