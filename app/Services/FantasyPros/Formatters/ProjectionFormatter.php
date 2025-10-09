<?php

namespace App\Services\FantasyPros\Formatters;

use App\Facades\Action;
use App\Models\Player;
use App\Models\PlayerMissing;
use App\Services\FantasyPros\Data\PlayerData;
use Illuminate\Support\Collection;

class ProjectionFormatter
{
    private array $data = [];

    public function __construct(protected array|Collection $players, protected int $season, protected int $week)
    {
        if (! $this->players instanceof Collection) {
            $this->players = collect($this->players);
        }
    }

    public static function from(array|Collection $players, int $season, int $week)
    {
        $formatter = new ProjectionFormatter($players, $season, $week);

        return $formatter->getFormatted();
    }

    public function getFormatted()
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

        if (! $playerModel instanceof Player) {
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
        $playerModel = Player::query()
            ->where('fp_id', $player->player_id)
            ->first();

        if (! $playerModel instanceof Player) {
            $playerModel = Player::query()
                ->where('full_name', $player->player_name)
                ->first();
        }

        if (! $playerModel instanceof Player) {
            Action::model(PlayerMissing::class)->upsert(
                data: $player->toArray(),
                source: get_called_class(),
            );

            dump([
                'player' => 'Not Found',
                'fp_id' => $player->player_id,
                'player_name' => $player->player_name,
                'player_position_id' => $player->player_position_id,
                'player_team_id' => $player->player_team_id,
            ]);

            return null;
        }

        return $playerModel;
    }
}
