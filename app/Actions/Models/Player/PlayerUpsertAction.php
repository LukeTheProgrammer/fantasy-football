<?php

namespace App\Actions\Models\Player;

use App\Exceptions\AmbiguousPlayerException;
use App\Models\Player;
use Illuminate\Support\Arr;

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

        $fullName = Arr::get($data, 'full_name');
        $fullNameQuery = Player::where('full_name', '=', $fullName);

        if ($fullNameQuery->count() === 1) {
            return $fullNameQuery->first();
        }

        $nameQuery = Player::query()
            ->orWhere(fn ($q) => $q->nameLike(Arr::get($data, 'first_name')))
            ->orWhere(fn ($q) => $q->nameLike(Arr::get($data, 'last_name')));

        if ($nameQuery->count() === 1) {
            return $nameQuery->first();
        }

        if ($fullNameQuery->count() > 1 || $nameQuery->count() > 1) {
            throw new AmbiguousPlayerException('Multiple players found for ' . Arr::get($data, 'full_name'));
        }

        return null;
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
