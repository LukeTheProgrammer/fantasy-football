<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class CredentialsData extends BaseData
{
    public function __construct(
        // CBS league ids are slugs, not integers (e.g. "crf1466606121").
        public string $leagueId,
        // Scraped from CBSi.token on any league page. Short lived.
        public ?string $accessToken = null,
    ) {
        //
    }
}
