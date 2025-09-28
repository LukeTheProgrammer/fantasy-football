<?php

namespace App\Console\Commands;

use App\Facades\Action;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
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
        $positions = Position::all()->pluck('id', 'id');
        $teams = Team::all()->pluck('id', 'id');

        $data = [
            'position_id' => select('Position', $positions),
            'team_id' => select('Team', $teams),
            'first_name' => text('First Name'),
            'last_name' => text('Last Name'),
            'jersey_number' => text('Jersey Number', ''),
        ];

        $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];

        $player = Action::model(Player::class)->upsert($data);

        $this->info('Player created successfully! ' . $player->id);
    }
}
