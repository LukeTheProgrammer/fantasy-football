<?php

namespace App\Events;

use App\Models\Draft;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Picks arrived from ESPN. The room reloads itself when it hears this.
 */
class DraftPicksSynced implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Draft $draft,
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        public bool $isCompleted = false,
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
        return 'DraftPicksSynced';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'created'      => $this->created,
            'updated'      => $this->updated,
            'skipped'      => $this->skipped,
            'is_completed' => $this->isCompleted,
            'synced_at'    => now()->toIso8601String(),
        ];
    }
}
