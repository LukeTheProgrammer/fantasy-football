<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DraftFrameStoreRequest;
use App\Models\League;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

/**
 * Frames tapped from the ESPN draft room by the browser extension.
 *
 * ESPN evicts a team from the draft room when a second socket joins as that
 * team, so the app cannot connect while a draft is live; the extension in
 * extension/espn-draft-tap watches the room's own socket and posts what it
 * sees here. Nothing is parsed yet — the frame protocol is undocumented, and
 * the log is what the decoding work reads.
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

        return response()->json(['written' => $written]);
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
