<?php

namespace App\Http\Resources;

use App\Models\DraftPick;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerProjection;
use App\Models\Team;
use App\Models\Week;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeagueShowResource extends JsonResource
{
    protected ?Collection $playerProjections = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->playerProjections = PlayerProjection::forSeason($this->season)
            ->get()
            ->groupBy('player_id')
            ->map(fn ($projections) => $projections->keyBy('week'));

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'season'      => $this->season,
            'week'        => Week::current()->first()->week,
            'slug'        => $this->slug,
            'description' => $this->description,
            'platform'    => $this->platform,
            'platform_id' => $this->platform_id,
            'team_count'  => $this->team_count,
            'is_public'   => $this->is_public,
            'is_active'   => $this->is_active,
            'is_admin'    => $this->userIsAdmin($request->user()),
            'matchups'    => $this->getMatchups(),
            'members'     => $this->getMembers(),
            'creator'     => Arr::only($this->creator->toArray(), ['id', 'name', 'email']),
            'draft'       => $this->getDraft(),
            'settings'    => $this->settings,
        ];
    }

    /* ===[ GETTERS ]=== */

    private function getMatchups()
    {
        return $this->matchups->map(function ($matchup) {
            return [
                'id'                   => $matchup->id,
                'season'               => $matchup->season,
                'week'                 => $matchup->week,
                'home_score'           => $matchup->home_score,
                'away_score'           => $matchup->away_score,
                'home_projected_score' => $matchup->home_projected_score,
                'away_projected_score' => $matchup->away_projected_score,
                'home_team'            => $this->formatFantasyTeam($matchup->homeTeam),
                'away_team'            => $this->formatFantasyTeam($matchup->awayTeam),
            ];
        })->groupBy('week');
    }

    private function getMembers()
    {
        // Build ranked lists for points for/against
        $pf = $this->members
            ->map(fn ($m) => ['id' => $m->id, 'pf' => $m->points_for])
            ->sortByDesc(fn ($lm) => $lm['pf'])
            ->values()
            ->mapWithKeys(fn ($m, $i) => [$m['id'] => $i + 1]);

        $pa = $this->members
            ->map(fn ($m) => ['id' => $m->id, 'pa' => $m->points_against])
            ->sortByDesc(fn ($lm) => $lm['pa'])
            ->values()
            ->mapWithKeys(fn ($m, $i) => [$m['id'] => $i + 1]);

        return $this->members->map(function ($member) use ($pf, $pa) {
            $pfRank = $pf->get($member->id);
            $paRank = $pa->get($member->id);

            return [
                'id'                  => $member->id,
                'league_id'           => $member->league_id,
                'user_id'             => $member->user_id,
                'external_id'         => $member->external_id,
                'team_name'           => $member->team_name,
                'owner_name'          => $member->owner_name,
                'team_logo'           => $member->team_logo,
                'is_admin'            => $member->is_admin,
                'is_active'           => $member->is_active,
                'wins'                => $member->wins,
                'losses'              => $member->losses,
                'ties'                => $member->ties,
                'points_for'          => $member->points_for,
                'points_for_rank'     => $this->rankName($pfRank),
                'points_against'      => $member->points_against,
                'points_against_rank' => $this->rankName($paRank),
                'faab_balance'        => $member->faab_balance,
                'rosters'             => $this->formatRosters($member->rosters),
            ];
        });
    }

    private function rankName(int|string $rank)
    {
        return match ($rank) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $rank . 'th',
        };
    }

    private function getDraft()
    {
        $data = Arr::except($this->draft->toArray(), ['created_at', 'updated_at']);

        $data['picks'] = $this->formatDraftPicks($this->draft->picks);

        return $data;
    }

    /* ===[ FORMATTERS ]=== */

    private function formatFantasyTeam(?LeagueMember $team)
    {
        if (!$team) {
            return [];
        }

        return [
            'id'             => $team->id,
            'team_name'      => $team->team_name,
            'owner_name'     => $team->owner_name,
            'team_logo'      => $team->team_logo,
            'wins'           => $team->wins,
            'losses'         => $team->losses,
            'ties'           => $team->ties,
            'points_for'     => $team->points_for,
            'points_against' => $team->points_against,
            'faab_balance'   => $team->faab_balance,
        ];
    }

    private function formatRosters(Collection $rosters)
    {
        return $rosters->map(function (LeagueMemberRoster $roster) {
            $data = [
                'id'                    => $roster->id,
                'league_member_id'      => $roster->league_member_id,
                'player_id'             => $roster->player_id,
                'nfl_game_id'           => $roster->nfl_game_id,
                'season'                => $roster->season,
                'week'                  => $roster->week,
                'lineup_slot_id'        => $roster->lineup_slot_id,
                'position_rank'         => $roster->position_rank,
                'overall_rank'          => $roster->overall_rank,
                'percent_owned'         => $roster->percent_owned,
                'percent_started'       => $roster->percent_started,
                'fantasy_points'        => $roster->fantasy_points,
                'nfl_game'              => $this->formatNflGame($roster->nflGame),
                'player'                => $this->formatPlayer($roster->player),
                'player_projection'     => $this->getPlayerProjection($roster->player, $roster->week),
            ];

            $points = Arr::get($data, 'fantasy_points', 0);
            $espn = Arr::get($data, 'player_projection.espn_points', 0);
            $fp = Arr::get($data, 'player_projection.fp_points', 0);

            $data['espn_diff'] = ($points > 0 && $espn > 0) ? round($points - $espn, 2) : 0;
            $data['fp_diff'] = ($points > 0 && $fp > 0) ? round($points - $fp, 2) : 0;

            return $data;
        })->groupBy('week');
    }

    private function formatPlayer(?Player $player)
    {
        if (!$player) {
            return [];
        }

        return [
            'id'             => $player->id,
            'espn_id'        => $player->espn_id,
            'pfr_id'         => $player->pfr_id,
            'fp_id'          => $player->fp_id,
            'first_name'     => $player->first_name,
            'last_name'      => $player->last_name,
            'full_name'      => $player->full_name,
            'position'       => $player->position_id,
            'team'           => $player->team_id,
            'jersey'         => $player->jersey,
            'height'         => $player->height,
            'weight'         => $player->weight,
            'college'        => $player->college,
            'bye_week'       => $player->bye_week,
            'fantasy_points' => $player->fantasy_points,
            'headshot'       => $player->headshot,
        ];
    }

    private function getPlayerProjection(Player $player, int $week)
    {
        $projections = $this->playerProjections->get($player->id);
        $playerProjection = $projections?->get($week);

        if (! $playerProjection) {
            return [];
        }

        $data = [
            'id'          => $playerProjection->id,
            'player_id'   => $playerProjection->player_id,
            'nfl_game_id' => $playerProjection->nfl_game_id,
            'season'      => $playerProjection->season,
            'week'        => $playerProjection->week,
            'espn_points' => $playerProjection->espn_projected_points,

            'fp_points'   => $this->firstProjection([
                $playerProjection->fp_projected_points,
                $playerProjection->fp_half_projected_points,
                $playerProjection->fp_2qb_projected_points,
                $playerProjection->fp_ppr_projected_points,
            ]),
            'fp_pos_rank'   => $this->firstProjection([
                $playerProjection->fp_pos_rank,
                $playerProjection->fp_half_pos_rank,
                $playerProjection->fp_2qb_pos_rank,
                $playerProjection->fp_ppr_pos_rank,
            ]),
            'fp_pos_rank_min' => $this->firstProjection([
                $playerProjection->fp_pos_rank_min,
                $playerProjection->fp_half_pos_rank_min,
                $playerProjection->fp_2qb_pos_rank_min,
                $playerProjection->fp_ppr_pos_rank_min,
            ]),
            'fp_pos_rank_max' => $this->firstProjection([
                $playerProjection->fp_pos_rank_max,
                $playerProjection->fp_half_pos_rank_max,
                $playerProjection->fp_2qb_pos_rank_max,
                $playerProjection->fp_ppr_pos_rank_max,
            ]),
            'fp_pos_rank_avg' => $this->firstProjection([
                $playerProjection->fp_pos_rank_avg,
                $playerProjection->fp_half_pos_rank_avg,
                $playerProjection->fp_2qb_pos_rank_avg,
                $playerProjection->fp_ppr_pos_rank_avg,
            ]),
            'fp_pos_rank_std' => $this->firstProjection([
                $playerProjection->fp_pos_rank_std,
                $playerProjection->fp_half_pos_rank_std,
                $playerProjection->fp_2qb_pos_rank_std,
                $playerProjection->fp_ppr_pos_rank_std,
            ]),

            'fp_2qb_projected_points' => $playerProjection->fp_2qb_projected_points,
            'fp_2qb_pos_rank' => $playerProjection->fp_2qb_pos_rank,
            'fp_2qb_pos_rank_min' => $playerProjection->fp_2qb_pos_rank_min,
            'fp_2qb_pos_rank_max' => $playerProjection->fp_2qb_pos_rank_max,
            'fp_2qb_pos_rank_avg' => $playerProjection->fp_2qb_pos_rank_avg,
            'fp_2qb_pos_rank_std' => $playerProjection->fp_2qb_pos_rank_std,

            'fp_ppr_projected_points' => $playerProjection->fp_ppr_projected_points,
            'fp_ppr_pos_rank' => $playerProjection->fp_ppr_pos_rank,
            'fp_ppr_pos_rank_min' => $playerProjection->fp_ppr_pos_rank_min,
            'fp_ppr_pos_rank_max' => $playerProjection->fp_ppr_pos_rank_max,
            'fp_ppr_pos_rank_avg' => $playerProjection->fp_ppr_pos_rank_avg,
            'fp_ppr_pos_rank_std' => $playerProjection->fp_ppr_pos_rank_std,

            'fp_half_projected_points' => $playerProjection->fp_half_projected_points,
            'fp_half_pos_rank' => $playerProjection->fp_half_pos_rank,
            'fp_half_pos_rank_min' => $playerProjection->fp_half_pos_rank_min,
            'fp_half_pos_rank_max' => $playerProjection->fp_half_pos_rank_max,
            'fp_half_pos_rank_avg' => $playerProjection->fp_half_pos_rank_avg,
            'fp_half_pos_rank_std' => $playerProjection->fp_half_pos_rank_std,

            'fp_projected_points' => $playerProjection->fp_projected_points,
            'fp_pos_rank' => $playerProjection->fp_pos_rank,
            'fp_pos_rank_min' => $playerProjection->fp_pos_rank_min,
            'fp_pos_rank_max' => $playerProjection->fp_pos_rank_max,
            'fp_pos_rank_avg' => $playerProjection->fp_pos_rank_avg,
            'fp_pos_rank_std' => $playerProjection->fp_pos_rank_std,
        ];

        return $data;
    }

    private function firstProjection(array $data)
    {
        foreach ($data as $proj) {
            if (! empty($proj) && $proj > 0) {
                return $proj;
            }
        }

        return 0;
    }

    private function formatDraftPicks(Collection $picks)
    {
        return $picks->map(function (DraftPick $pick) {
            return [
                'id'                  => $pick->id,
                'draft_id'            => $pick->draft_id,
                'round'               => $pick->round,
                'pick_number'         => $pick->pick_number,
                'overall_pick_number' => $pick->overall_pick_number,
                'amount'              => $pick->amount,
                'is_keeper'           => $pick->is_keeper,
                'league_member'       => Arr::except($pick->leagueMember->toArray(), ['created_at', 'updated_at']),
                'player'              => Arr::except($pick->player->toArray(), ['created_at', 'updated_at']),
            ];
        });
    }

    private function formatNflGame(?NflGame $game)
    {
        if (!$game) {
            return [];
        }

        $gameTime = Carbon::parse($game->starts_at);

        return [
            'id'           => $game->id,
            'espn_id'      => $game->espn_id,
            'season'         => $game->season,
            'week'         => $game->week,
            'starts_at'    => $game->starts_at,
            'day'          => $gameTime->format('D'),
            'time'         => $gameTime->format('g:i'),
            'home_score'   => $game->home_score,
            'away_score'   => $game->away_score,
            'is_completed' => $game->is_completed,
            'is_playoff'   => $game->is_playoff,
            'is_bye'       => $game->is_bye,
            'home_team'    => $this->formatNflTeam($game->homeTeam),
            'away_team'    => $this->formatNflTeam($game->awayTeam),
        ];
    }

    private function formatNflTeam(?Team $team)
    {
        if (!$team) {
            return [];
        }

        return [
            'id'           => $team->id,
            'espn_id'      => $team->espn_id,
            'pfr_id'       => $team->pfr_id,
            'abbreviation' => $team->abbreviation,
            'location'     => $team->location,
            'name'         => $team->name,
            'logo'         => $team->logo,
            'conference'   => $team->conference,
            'division'     => $team->division,
        ];
    }
}
