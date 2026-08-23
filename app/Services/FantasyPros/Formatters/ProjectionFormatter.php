<?php

namespace App\Services\FantasyPros\Formatters;

use App\Facades\Player as PlayerFacade;
use App\Models\Player;
use App\Services\FantasyPros\Data\PlayerData;
use Illuminate\Support\Collection;

class ProjectionFormatter
{
    private array $data = [];

    public function __construct(protected array|Collection $players, protected int $season, protected int $week)
    {
        if (!$this->players instanceof Collection) {
            $this->players = collect($this->players);
        }
    }

    public static function from(array|Collection $players, int $season, int $week)
    {
        $formatter = new ProjectionFormatter($players, $season, $week);

        return $formatter->format();
    }

    public function format()
    {
        $this->formatData();

        return $this->data;
    }

    public function formatData()
    {
        $this->players->map(fn ($p) => PlayerData::from($p))
            ->each(fn (PlayerData $player) => $this->formatPlayer($player));
    }

    private function formatPlayer(PlayerData $player)
    {
        $playerModel = $this->findPlayerModel($player);

        if (!$playerModel instanceof Player) {
            return;
        }

        $this->data[] = [
            'season'           => $this->season,
            'week'             => $this->week,
            'player_id'        => $playerModel->id,
            'projected_points' => $player->r2p_pts,
            'pos_rank'         => $player->pos_rank,
            'pos_rank_min'     => $player->rank_min,
            'pos_rank_max'     => $player->rank_max,
            'pos_rank_avg'     => $player->rank_ave,
            'pos_rank_std'     => $player->rank_std,
        ];
    }

    private function findPlayerModel(PlayerData $player): ?Player
    {
        return PlayerFacade::find([
            'fp_id'       => $player->player_id,
            'full_name'   => $player->player_name,
            'position_id' => $player->player_position_id,
            'team_id'     => $player->player_team_id,
        ], ['source' => static::class]);
    }
}
