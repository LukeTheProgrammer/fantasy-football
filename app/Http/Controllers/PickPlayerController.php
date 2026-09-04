<?php

namespace App\Http\Controllers;

use App\Facades\Pick as PickFacade;
use App\Facades\Ranking as RankingFacade;
use App\Models\Draft;
use App\Models\Player;
use Illuminate\Http\JsonResponse;

class PickPlayerController extends Controller
{
    /**
     * The profile behind a player's name in the pick room.
     */
    public function show(Draft $draft, Player $player): JsonResponse
    {
        $this->authorize('view', $draft);

        [$ppr, $superflex] = RankingFacade::format($draft->league);

        return response()->json(PickFacade::playerProfile($draft, $player, $ppr, $superflex));
    }
}
