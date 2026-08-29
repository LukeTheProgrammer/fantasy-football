<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DraftFrameStoreRequest;
use App\Jobs\ProcessDraftFramesJob;
use App\Models\League;
use App\Services\Espn\Helpers\DraftFrameParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

/**
 * Frames tapped from the ESPN draft room by the browser extension.
 *
 * ESPN evicts a team from the draft room when a second socket joins as that
 * team, so the app cannot connect while a draft is live; the extension in
 * extension/espn-draft-tap watches the room's own socket and posts what it
 * sees here. Every frame is logged as it arrives — the protocol is
 * undocumented, so the log stays the record the decoding work reads — and the
 * sales among them are queued for the board.
 */
class DraftFrameController extends Controller
{
    public function store(DraftFrameStoreRequest $request): JsonResponse
    {
        $frames = $request->validated('frames');

        $written = 0;

        foreach ($frames as $frame) {
            $path = $this->logPath(Arr::get($frame, 'url'));

            $line = implode(' ', [
                date('Y-m-d H:i:s', intdiv((int) Arr::get($frame, 'at'), 1000)),
                strtoupper(Arr::get($frame, 'direction')),
                Arr::get($frame, 'encoding'),
                Arr::get($frame, 'frame'),
            ]);

            file_put_contents($path, $line . PHP_EOL, FILE_APPEND);

            $written++;
        }

        ProcessDraftFramesJob::dispatch(
            $this->espnLeagueId($frames),
            $this->soldFrames($frames),
        );

        return response()->json(['written' => $written]);
    }

    /**
     * The text of every SOLD frame in the batch.
     *
     * Only what the room received counts: a frame the browser sent is this
     * client's own bid, not a completed sale.
     *
     * @param array<int, array<string, mixed>> $frames
     *
     * @return array<int, string>
     */
    protected function soldFrames(array $frames): array
    {
        return collect($frames)
            ->where('direction', 'recv')
            ->where('encoding', 'text')
            ->pluck('frame')
            ->filter(fn ($frame) => DraftFrameParser::sold((string) $frame) !== null)
            ->values()
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $frames
     */
    protected function espnLeagueId(array $frames): int
    {
        return (int) collect($frames)
            ->map(fn ($frame) => DraftFrameParser::leagueId(Arr::get($frame, 'url')))
            ->filter()
            ->first();
    }

    /**
     * The socket url carries the ESPN league id, which names the log after the
     * local league where there is one, so the extension's frames and the
     * listener command's land in the same file.
     */
    protected function logPath(?string $url): string
    {
        preg_match('/league-(\d+)/', (string) $url, $matches);

        $platformId = $matches[1] ?? null;

        $league = $platformId
            ? League::where('platform_id', $platformId)->latest('season')->first()
            : null;

        $name = $league instanceof League ? $league->id : ($platformId ?? 'unknown');

        return storage_path('logs/espn-draft-' . $name . '.log');
    }
}
