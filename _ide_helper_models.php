<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $league_id
 * @property \Illuminate\Support\Carbon|null $draft_date
 * @property string $draft_type
 * @property bool $is_completed
 * @property int|null $auction_budget
 * @property int|null $current_pick
 * @property int|null $current_round
 * @property int $time_per_pick
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\League $league
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DraftPick> $picks
 * @property-read int|null $picks_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereAuctionBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereCurrentPick($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereCurrentRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereDraftDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereDraftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereLeagueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereTimePerPick($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Draft whereUpdatedAt($value)
 */
	class Draft extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $draft_id
 * @property int $league_member_id
 * @property int|null $player_id
 * @property int $round
 * @property int $pick_number
 * @property int $overall_pick_number
 * @property numeric|null $amount
 * @property bool $is_keeper
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Draft $draft
 * @property-read \App\Models\LeagueMember $leagueMember
 * @property-read \App\Models\Player|null $player
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereDraftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereIsKeeper($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereLeagueMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereOverallPickNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick wherePickNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftPick whereUpdatedAt($value)
 */
	class DraftPick extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $player_id
 * @property int $year
 * @property string $ranked_at
 * @property string $type
 * @property string|null $source
 * @property string $ppr
 * @property int|null $rank
 * @property int|null $tier
 * @property numeric|null $adp
 * @property string|null $adv
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Player $player
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking forSeason(int $year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking fromSource(string $source)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking orderByRanking()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereAdp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereAdv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking wherePpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereRankedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DraftRanking whereYear($value)
 */
	class DraftRanking extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $year
 * @property int $league_id
 * @property int $player_id
 * @property string $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\League $league
 * @property-read \App\Models\Player $player
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason whereLeagueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsSeason whereYear($value)
 */
	class FantasyPointsSeason extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $year
 * @property int $week_number
 * @property int $nfl_game_id
 * @property int $league_id
 * @property int $player_id
 * @property string $espn_projected_points
 * @property string $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\League $league
 * @property-read \App\Models\Player $player
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereEspnProjectedPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereLeagueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereNflGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereWeekNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FantasyPointsWeek whereYear($value)
 */
	class FantasyPointsWeek extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $created_by_user_id
 * @property string $name
 * @property int $year
 * @property string $slug
 * @property string|null $description
 * @property string $platform
 * @property string|null $platform_id
 * @property int $team_count
 * @property bool $is_public
 * @property string|null $join_code
 * @property bool $is_active
 * @property array<array-key, mixed>|null $credentials
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\Draft|null $draft
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FantasyPointsSeason> $fantasyPointsSeasons
 * @property-read int|null $fantasy_points_seasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FantasyPointsWeek> $fantasyPointsWeeks
 * @property-read int|null $fantasy_points_weeks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeagueMember> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\LeagueSettings|null $settings
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereJoinCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereTeamCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|League withoutTrashed()
 */
	class League extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $league_id
 * @property int|null $user_id
 * @property string|null $external_id
 * @property string $team_name
 * @property string|null $owner_name
 * @property string|null $team_logo
 * @property bool $is_admin
 * @property bool $is_active
 * @property int $wins
 * @property int $losses
 * @property int $ties
 * @property string $points_for
 * @property string $points_against
 * @property int $faab_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DraftPick> $draftPicks
 * @property-read int|null $draft_picks_count
 * @property-read \App\Models\League $league
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeagueMemberRoster> $rosters
 * @property-read int|null $rosters_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember forExtId(string|int $extId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember forLeague(\App\Models\League|string|int $league)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereFaabBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereLeagueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereLosses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereOwnerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember wherePointsAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember wherePointsFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereTeamLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereTeamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereTies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember whereWins($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMember withoutTrashed()
 */
	class LeagueMember extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $league_member_id
 * @property int $player_id
 * @property \Illuminate\Support\Carbon $added_at
 * @property \Illuminate\Support\Carbon|null $dropped_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\LeagueMember $leagueMember
 * @property-read \App\Models\Player $player
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereAddedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereDroppedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereLeagueMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueMemberRoster withoutTrashed()
 */
	class LeagueMemberRoster extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $league_id
 * @property array<array-key, mixed> $roster_positions
 * @property int $roster_size
 * @property int $starters_count
 * @property int $bench_count
 * @property int $ir_spots
 * @property string $passing_points_per_yard
 * @property numeric $passing_td_points
 * @property numeric $interception_points
 * @property string $rushing_points_per_yard
 * @property numeric $rushing_td_points
 * @property string $receiving_points_per_yard
 * @property numeric $receiving_td_points
 * @property numeric $reception_points
 * @property numeric $fumble_lost_points
 * @property numeric $two_point_conversion_points
 * @property numeric $field_goal_0_39_points
 * @property numeric $field_goal_40_49_points
 * @property numeric $field_goal_50_plus_points
 * @property numeric $extra_point_points
 * @property numeric $defense_sack_points
 * @property numeric $defense_interception_points
 * @property numeric $defense_fumble_recovery_points
 * @property numeric $defense_td_points
 * @property numeric $defense_safety_points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\League $league
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereBenchCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereDefenseFumbleRecoveryPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereDefenseInterceptionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereDefenseSackPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereDefenseSafetyPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereDefenseTdPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereExtraPointPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereFieldGoal039Points($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereFieldGoal4049Points($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereFieldGoal50PlusPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereFumbleLostPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereInterceptionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereIrSpots($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereLeagueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings wherePassingPointsPerYard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings wherePassingTdPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereReceivingPointsPerYard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereReceivingTdPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereReceptionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereRosterPositions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereRosterSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereRushingPointsPerYard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereRushingTdPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereStartersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereTwoPointConversionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeagueSettings whereUpdatedAt($value)
 */
	class LeagueSettings extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $espn_id
 * @property int $home_team_id
 * @property int $away_team_id
 * @property int $year
 * @property int $week
 * @property string|null $starts_at
 * @property int|null $home_score
 * @property int|null $away_score
 * @property int $is_completed
 * @property int $is_playoff
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Team $awayTeam
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FantasyPointsWeek> $fantasyPointsWeeks
 * @property-read int|null $fantasy_points_weeks_count
 * @property-read \App\Models\Team $homeTeam
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereAwayScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereAwayTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereEspnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereHomeScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereHomeTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereIsPlayoff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NflGame whereYear($value)
 */
	class NflGame extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $espn_id
 * @property int $position_id
 * @property int $team_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $full_name
 * @property int|null $jersey_number
 * @property string|null $height
 * @property string|null $weight
 * @property string|null $college
 * @property \Illuminate\Support\Carbon|null $draft_year
 * @property string|null $draft_round
 * @property string|null $draft_pick
 * @property string|null $draft_team
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string|null $headshot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $age
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerAlias> $aliases
 * @property-read int|null $aliases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DraftRanking> $currentDraftRankings
 * @property-read int|null $current_draft_rankings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DraftPick> $draftPicks
 * @property-read int|null $draft_picks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DraftRanking> $draftRankings
 * @property-read int|null $draft_rankings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FantasyPointsSeason> $fantasyPointsSeasons
 * @property-read int|null $fantasy_points_seasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FantasyPointsWeek> $fantasyPointsWeeks
 * @property-read int|null $fantasy_points_weeks_count
 * @property-read mixed $is_first_round_pick
 * @property-read mixed $is_rookie
 * @property-read \App\Models\Position $position
 * @property-read \App\Models\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player espnId(string|int $espnId)
 * @method static \Database\Factories\PlayerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player nameLike(string $name)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftPick($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDraftYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereEspnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereHeadshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereJerseyNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player withoutTrashed()
 */
	class Player extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $player_id
 * @property int|null $team_id
 * @property int|null $position_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Player $player
 * @property-read \App\Models\Position|null $position
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerAlias whereUpdatedAt($value)
 */
	class PlayerAlias extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $abbreviation
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player> $players
 * @property-read int|null $players_count
 * @method static \Database\Factories\PositionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position forAbbreviation(string $abbreviation)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position withoutTrashed()
 */
	class Position extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $espn_id
 * @property string $abbreviation
 * @property string $location
 * @property string $name
 * @property string|null $logo
 * @property string $conference
 * @property string $division
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player> $players
 * @property-read int|null $players_count
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team forAbbreviation(string $abbreviation)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team forEspnId(int $espnId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereConference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereEspnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withoutTrashed()
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\League> $createdLeagues
 * @property-read int|null $created_leagues_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Draft> $drafts
 * @property-read int|null $drafts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeagueMember> $leagueMemberships
 * @property-read int|null $league_memberships_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\League> $leagues
 * @property-read int|null $leagues_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

