<?php

namespace App\Events;

use App\Models\Draft;
use App\Models\LeagueMember;
use App\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A player is up for bid. The room puts him in the nomination bar.
 *
 * Sent once per nomination rather than per bid: the price moves several times
 * a second and the ESPN room is where the bidding is watched, so the board
 * only needs to know who is up.
 */
class PlayerNominated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Draft $draft,
        public Player $player,
        public ?LeagueMember $member = null,
        public float $amount = 0,
    ) {
        //
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('draft.' . $this->draft->id)];
    }

    public function broadcastAs(): string
    {
        return 'PlayerNominated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'player_id'        => $this->player->id,
            'league_member_id' => $this->member?->id,
            'amount'           => $this->amount,
            'nominated_at'     => now()->toIso8601String(),
        ];
    }
}
