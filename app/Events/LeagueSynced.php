<?php

namespace App\Events;

use App\Models\League;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A league sync finished, either way. The page stops waiting when it hears
 * this, so a failure is broadcast as loudly as a success.
 */
class LeagueSynced implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public League $league,
        public string $status = 'completed',
        public ?string $message = null,
    ) {
        //
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('league.' . $this->league->id)];
    }

    public function broadcastAs(): string
    {
        return 'LeagueSynced';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'status'    => $this->status,
            'message'   => $this->message,
            'synced_at' => now()->toIso8601String(),
        ];
    }
}
