<?php

namespace App\Actions\Models\Player;

use App\Models\Player;
use Illuminate\Support\Arr;

class PlayerUpdateAction
{
    public function run(Player $player, array $playerData = []): Player
    {
        $data = $this->formatData($playerData);

        $player->update($data);

        return $player;
    }

    private function formatData(array $data = []): array
    {
        return array_filter([
            'espn_id'       => Arr::get($data, 'espn_id'),
            'pfr_id'        => Arr::get($data, 'pfr_id'),
            'position_id'   => Arr::get($data, 'position_id'),
            'team_id'       => Arr::get($data, 'team_id'),
            'first_name'    => Arr::get($data, 'first_name'),
            'last_name'     => Arr::get($data, 'last_name'),
            'full_name'     => Arr::get($data, 'full_name'),
            'jersey_number' => Arr::get($data, 'jersey_number'),
            'draft_year'    => Arr::get($data, 'draft_year'),
            'draft_round'   => Arr::get($data, 'draft_round'),
            'draft_pick'    => Arr::get($data, 'draft_pick'),
            'draft_team'    => Arr::get($data, 'draft_team'),
            'birth_date'    => Arr::get($data, 'birth_date'),
            'headshot'      => Arr::get($data, 'headshot'),
            'height'        => Arr::get($data, 'height'),
            'weight'        => Arr::get($data, 'weight'),
            'college'       => Arr::get($data, 'college'),
        ]);
    }
}
