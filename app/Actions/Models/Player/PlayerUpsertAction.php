<?php

namespace App\Actions\Models\Player;

use App\Exceptions\AmbiguousPlayerException;
use App\Facades\Action;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\PlayerTeam;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PlayerUpsertAction
{
    public function run(array $playerData = []): Player
    {
        $data = $this->formatData($playerData);

        $player = $this->findPlayer($data);

        if ($player instanceof Player) {
            $player->update($data);
        } else{
            $player = Player::create($data);
        }

        if ($teamId = Arr::get($playerData, 'team_id', false)) {
            $this->upsertPlayerTeam($player, $teamId);
        }

        return $player;
    }

    private function findPlayer(array $data = []): ?Player
    {
        $espnId = Arr::get($data, 'espn_id');

        if (! empty($espnId)) {
            $espnQuery = Player::espnId($espnId);

            if ($espnQuery->count() === 1) {
                return $espnQuery->first();
            }
        }

        $pfrId = Arr::get($data, 'pfr_id');

        if (! empty($pfrId)) {
            $pfrQuery = Player::pfrId($pfrId);

            if ($pfrQuery->count() === 1) {
                return $pfrQuery->first();
            }
        }

        $fpId = Arr::get($data, 'fp_id');

        if (! empty($fpId)) {
            $fpQuery = Player::fpId($fpId);

            if ($fpQuery->count() === 1) {
                return $fpQuery->first();
            }
        }

        $fullName = Arr::get($data, 'full_name');
        $fullNameQuery = Player::where('full_name', '=', $fullName);

        if ($fullNameQuery->count() === 1) {
            return $fullNameQuery->first();
        }

        if ($fullNameQuery->count() > 1) {
            $fn = $fullNameQuery->get()->pluck('id')->toArray();

            throw new AmbiguousPlayerException(
                'Multiple players found for ' . Arr::get($data, 'full_name') . ' ' . json_encode($fn)
            );
        }

        $aliasQuery = PlayerAlias::forName($fullName);

        if ($aliasQuery->count() === 1) {
            return $aliasQuery->first()->player;
        }

        return null;
    }

    private function formatData(array $data = []): array
    {
        return array_filter([
            'espn_id'       => Arr::get($data, 'espn_id'),
            'pfr_id'        => Arr::get($data, 'pfr_id'),
            'fp_id'         => Arr::get($data, 'fp_id'),
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

    private function upsertPlayerTeam(Player $player, int|string $teamId): PlayerTeam
    {
        return Action::model(PlayerTeam::class)->upsert($player, $teamId);
    }
}
