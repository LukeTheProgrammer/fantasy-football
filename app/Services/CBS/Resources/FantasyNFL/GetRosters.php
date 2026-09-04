<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetRosters extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/rosters';
    }

    /**
     * With no team CBS answers with the calling user's own roster only.
     */
    public function setOpts(int|string|null $teamId = null): static
    {
        $this->teamId = $teamId;

        if ($teamId) {
            $this->defaultQuery['team_id'] = $teamId;
        }

        return $this;
    }
}
