<?php

namespace App\Http\Controllers;

use App\Facades\Auction;
use App\Models\Draft;
use App\Models\Player;
use Illuminate\Http\JsonResponse;

class AuctionPlayerController extends Controller
{
    /**
     * The profile behind a player's name in the draft room: price history,
     * expert spread and what is left at his position.
     *
     * Fetched on demand rather than shipped with the board, because the room
     * only ever looks at one player at a time.
     */
    public function show(Draft $draft, Player $player): JsonResponse
    {
        return response()->json(Auction::playerProfile($draft, $player));
    }
}
