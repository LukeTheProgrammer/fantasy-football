<?php

namespace App\Events;

use App\Models\Draft;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * The sync loop ended. A reason is carried so the room can say whether the
 * draft finished, someone stopped it, or the platform stopped answering.
 */
class DraftSyncStopped implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Draft $draft,
        public string $reason = 'stopped',
        public ?string $message = null,
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
        return 'DraftSyncStopped';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'reason'     => $this->reason,
            'message'    => $this->message,
            'stopped_at' => now()->toIso8601String(),
        ];
    }
}
