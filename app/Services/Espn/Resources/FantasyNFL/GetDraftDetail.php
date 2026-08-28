<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Enums\FantasyNFLViews;
use App\Services\Espn\Extractors\FantasyDraftExtractor;
use App\Services\Espn\Formatters\FantasyDraftFormatter;
use Illuminate\Http\Client\Response;

/**
 * The draft picks alone.
 *
 * GetLeague asks for every view ESPN publishes, which is far too much to pull
 * every few seconds while a draft is running, so this asks for mDraftDetail
 * and nothing else.
 */
class GetDraftDetail extends FantasyNFLResource
{
    public int|string|null $season = null;

    public function setOpts(int|string|null $season = null)
    {
        $this->season = $season;
    }

    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'draft-detail',
            $this->season ?? $this->apiYear->value,
            date('Y-m-d'),
        ];

        // EX: data/espn/ffl/league-123456/formatted/draft-detail-2026-2026-08-28.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl(
            [FantasyNFLViews::DRAFT],
            null,
            $this->season ? (int) $this->season : null
        );

        $response = $this->get($url, null, $this->cookies);

        return $this->returnResponse($response);
    }

    public function returnExtracted(array|Response $response)
    {
        return FantasyDraftExtractor::from($response);
    }

    public function returnFormatted(array|Response $response)
    {
        return FantasyDraftFormatter::from(
            $this->returnExtracted($response)
        );
    }
}
