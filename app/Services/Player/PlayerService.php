<?php

namespace App\Services\Player;

use App\Services\Player\Resources\PlayerFinder;

class PlayerService
{
    public function find(array $data, array $opts = [])
    {
        $finder = new PlayerFinder($data, $opts);

        return $finder->player();
    }
}
