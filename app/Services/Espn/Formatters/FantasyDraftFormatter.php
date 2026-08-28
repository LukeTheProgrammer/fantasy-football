<?php

namespace App\Services\Espn\Formatters;

use App\Services\Espn\Data\FantasyNFL\DraftDetailData;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Helpers\DraftPickMapper;

/**
 * The mDraftDetail view on its own: every pick made so far, plus whether the
 * draft is still running. This is what the live sync polls.
 */
class FantasyDraftFormatter
{
    public function __construct(protected ResourceLeagueData $league)
    {
        //
    }

    public static function from(array|ResourceLeagueData $data)
    {
        if (!$data instanceof ResourceLeagueData) {
            $data = ResourceLeagueData::from($data);
        }

        return (new FantasyDraftFormatter($data))->format();
    }

    /**
     * @return array<string, mixed>
     */
    public function format(): array
    {
        /** @var DraftDetailData $draftDetail */
        $draftDetail = $this->league->draftDetail;

        return [
            'is_completed' => (bool) $draftDetail->drafted,
            'in_progress'  => (bool) $draftDetail->inProgress,
            'draftPicks'   => DraftPickMapper::map($draftDetail),
        ];
    }
}
