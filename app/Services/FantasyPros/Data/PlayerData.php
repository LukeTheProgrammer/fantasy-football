<?php

namespace App\Services\FantasyPros\Data;

class PlayerData extends BaseData
{
    public function __construct(
        public int|string|null $player_id,
        public ?float $player_ecr_delta,
        public ?float $player_owned_avg,
        public ?float $r2p_pts,
        public ?float $rank_ave,
        public ?float $rank_max,
        public ?float $rank_min,
        public ?float $rank_std,
        public ?int $player_bye_week,
        public ?int $rank_ecr,
        public ?string $note,
        public ?string $player_eligibility,
        public ?string $player_filename,
        public ?string $player_name,
        public ?string $player_opponent,
        public ?string $player_opponent_id,
        public ?string $player_page_url,
        public ?string $player_position_id,
        public ?string $player_positions,
        public ?string $player_short_name,
        public ?string $player_team_id,
        public ?string $pos_rank,
        public ?string $recommendation,
        public ?string $start_sit_grade,
        public ?string $tag,
    ) {
        //
    }
}
