<?php

namespace App\Services\Player\Resources;

use App\Models\Player;
use App\Models\PlayerAlias;
use Illuminate\Support\Arr;

class PlayerFinder
{
    private ?Player $player = null;

    private bool $createMissing = true;

    public function __construct(private array $data, private array $opts = [])
    {
        $this->createMissing = Arr::get($opts, 'create_missing', true);
    }

    public function player(): ?Player
    {
        $this->searchByName();

        return $this->player;
    }

    private function searchByName(): void
    {
        if ($this->player instanceof Player) {
            return;
        }

        $name = Arr::get($this->data, 'full_name');

        if (empty($name)) {
            return;
        }

        $playerQuery = Player::where('full_name', '=', $name);

        if ($playerQuery->count() === 1) {
            $this->player = $playerQuery->first();
            return;
        }

        $aliasQuery = PlayerAlias::where('name', '=', $name);

        if ($aliasQuery->count() === 1) {
            $this->player = $aliasQuery->first()->player;
            return;
        }
    }


}
