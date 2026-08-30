<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DraftFrameStoreRequest;
use App\Jobs\ProcessDraftFramesJob;
use App\Models\League;
use App\Services\Espn\Helpers\DraftFrameParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Frames tapped from the ESPN draft room by the browser extension.
 *
 * ESPN evicts a team from the draft room when a second socket joins as that
 * team, so the app cannot connect while a draft is live; the extension in
 * extension/espn-draft-tap watches the room's own socket and posts what it
 * sees here. Every frame is logged as it arrives, bar the noise listed in
 * NOISE — the protocol is undocumented, so the log stays the record the
 * decoding work reads — and the sales among them are queued for the board.
 */
class DraftFrameController extends Controller
{
    /**
     * Frame verbs that are dropped rather than logged.
     */
    protected const NOISE = ['CLOCK'];

    public function store(DraftFrameStoreRequest $request): JsonResponse
    {
        $frames = $request->validated('frames');

        $written = 0;

        foreach ($frames as $frame) {
            if ($this->isNoise($frame)) {
                continue;
            }

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
            $this->roomFrames($frames),
        );

        return response()->json(['written' => $written]);
    }

    /**
     * Frames not worth a line in the log.
     *
     * CLOCK ticks several times a second for the whole of a draft and says
     * nothing the board or the decoding work needs -- keeping them buries the
     * frames that do matter in a log that is read by eye.
     *
     * @param array<string, mixed> $frame
     */
    protected function isNoise(array $frame): bool
    {
        return Str::startsWith((string) Arr::get($frame, 'frame'), self::NOISE);
    }

    /**
     * The text of every frame in the batch the board reads: the sales, and the
     * bids that name the player currently up.
     *
     * Only what the room received counts: a frame the browser sent is this
     * client's own bid, not something the room has agreed to.
     *
     * @param array<int, array<string, mixed>> $frames
     *
     * @return array<int, string>
     */
    protected function roomFrames(array $frames): array
    {
        return collect($frames)
            ->where('direction', 'recv')
            ->where('encoding', 'text')
            ->pluck('frame')
            ->filter(fn ($frame) => DraftFrameParser::sold((string) $frame) !== null
                || DraftFrameParser::bid((string) $frame) !== null)
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
            ? League::where('platform_id', $platformId)->latest('season_id')->first()
            : null;

        $name = $league instanceof League ? $league->id : ($platformId ?? 'unknown');

        return storage_path('logs/espn-draft-' . $name . '.log');
    }
}
