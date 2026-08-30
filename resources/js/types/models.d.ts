export interface Draft {
  id: number;
  league_id: number;
  draft_date: string;
  draft_type: string;
  is_completed: boolean;
  auction_budget: number | null;
  current_pick: number | null;
  current_round: number | null;
  time_per_pick: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  league: League;
  picks: DraftPick[];
}

export interface DraftPick {
  id: number;
  draft_id: number;
  league_member_id: number;
  player_id: number;
  pick_number: number;
  round: number;
  amount: string;
  is_keeper: boolean;
  previous_season_cost: string;
  pick_time: string;
  created_at: string;
  updated_at: string;
  draft: Draft;
  leagueMember: LeagueMember;
  player: Player;
}

export interface AuctionPlayer {
  player_id: number;
  full_name: string | null;
  position_id: string;
  team_id: string | null;
  /** The week this player's NFL team does not play. */
  bye_week: number | null;
  headshot: string | null;
  rank: number | null;
  tier: number | null;
  projected_points: number | null;
  market_value: number | null;
  projected_value: number | null;
  /** Average auction value across the wider platform, stored unadjusted. */
  adv: number | null;
  previous_price: number | null;
  drafted_by: number | null;
  drafted_for: number | null;
  pick_id: number | null;
  season: number;
}

export interface PlayerPrice {
  season: number;
  amount: number | null;
  team: string | null;
  /** The highest price paid in that auction, for scale. */
  top: number;
}

export interface PlayerProfile {
  player: {
    id: number;
    full_name: string | null;
    position: string | null;
    team: string | null;
    jersey: number | null;
    height: string | null;
    weight: string | null;
    college: string | null;
    headshot: string | null;
    age: number | null;
    bye_week: number | null;
  };
  valuation: {
    rank: number | null;
    tier: number | null;
    projected_points: number | null;
    market_value: number | null;
    projected_value: number | null;
    adv: number | null;
    drafted_for: number | null;
    budget_share: number | null;
  };
  prices: PlayerPrice[];
  consensus: {
    pos_rank: number;
    min: number | null;
    max: number | null;
    average: number | null;
    std: number | null;
  } | null;
  position: {
    rank: number | null;
    tier_left: number | null;
    next: {
      player_id: number;
      full_name: string | null;
      rank: number | null;
      tier: number | null;
      market_value: number | null;
    }[];
  };
}

export interface RosterSlotPlayer {
  player_id: number;
  pick_id: number;
  full_name: string | null;
  position_id: string | null;
  team_id: string | null;
  amount: number;
  round: number;
  pick_number: number;
}

export interface RosterSlot {
  index: number;
  slot: string;
  label: string;
  is_starter: boolean;
  player: RosterSlotPlayer | null;
}

export interface BudgetRow {
  key: string;
  label: string;
  planned: number | null;
  actual: number | null;
  difference: number | null;
  filled_by: string | null;
}

export interface BudgetSuggestionPlayer {
  player_id: number;
  full_name: string | null;
  position_id: string | null;
  rank: number | null;
}

export interface BudgetSuggestion {
  /** The position the plan is built around. */
  focus: string;
  label: string;
  /** Dollars per budget row key, ready to apply to the plan. */
  allocations: Record<string, number>;
  /** Who the plan expects to buy in each slot, where it expects to buy anyone. */
  players: Record<string, BudgetSuggestionPlayer | null>;
  planned: number;
  unplanned: number;
  /** What the plan spends on the starting lineup. */
  starters: number;
}

export interface AuctionBudget {
  rows: BudgetRow[];
  budget: number;
  planned: number;
  unplanned: number;
  actual: number;
  remaining: number;
}

export interface AuctionTeam {
  id: number;
  team_name: string;
  owner_name: string | null;
  spent: number;
  remaining: number;
  filled: number;
  open_spots: number;
  max_bid: number;
}

export interface MarketPosition {
  position: string;
  drafted: number;
  available: number;
  top_tier: number | null;
  top_tier_left: number;
  slots_open: number;
  /** Flex spots that accept the position, held apart so they are not summed in. */
  flex_open: number;
  teams_needing: number;
  money_chasing: number;
}

export interface AuctionMarket {
  spent: number;
  expected: number;
  picks: number;
  /** Percent over or under what the board marked the players already sold. */
  inflation: number | null;
  money_left: number;
  spots_left: number;
  value_left: number;
  positions: MarketPosition[];
}

export interface RankingFormat {
  key: string;
  label: string;
  ppr: number;
  superflex: boolean;
  type: string;
}

export interface DraftRanking {
  id: number;
  player_id: number;
  season: number;
  ranked_at: string;
  type: string;
  source: string | null;
  ppr: string;
  rank: number | null;
  tier: number | null;
  adp: string | null;
  adv: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  player: Player;
}

export interface PlayerProjection {
  id: number;
  player_id: number;
  nfl_game_id: number | null;
  season: number;
  week: number;
  source: string;
  ppr: string;
  superflex: boolean;
  projected_points: string | null;
  pos_rank: number | null;
  pos_rank_min: number | null;
  pos_rank_max: number | null;
  pos_rank_avg: string | null;
  pos_rank_std: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  player: Player;
  nflGame: NflGame;
}

export interface SeasonOption {
  id: number;
  season: number;
}

/**
 * The platform credentials a league was imported with, as stored on the league
 * row. Each platform authenticates differently, so the shapes do not overlap
 * beyond the league id.
 */
export interface EspnCredentials {
  leagueId: number | string;
  s2: string;
  swid: string;
}

export interface CbsCredentials {
  leagueId: number | string;
  token: string;
}

export type LeagueCredentials = EspnCredentials | CbsCredentials;

export interface League {
  id: number;
  created_by_user_id: number;
  name: string;
  season_id: number;
  slug: string;
  description: string | null;
  platform: string;
  team_count: number;
  join_code: string;
  is_public: boolean;
  is_active: boolean;
  draft_date: string | null;
  credentials: LeagueCredentials | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  creator: User;
  draft: Draft;
  members: LeagueMember[];
  matchups: LeagueMatchup[];
  settings: LeagueSettings;
  seasons?: SeasonOption[];
}

export interface LeagueMember {
  id: number;
  league_id: number;
  user_id: number;
  team_name: string;
  owner_name: string | null;
  team_logo: string | null;
  draft_position: number | null;
  is_admin: boolean;
  is_active: boolean;
  wins: number;
  losses: number;
  ties: number;
  points_for: number;
  points_against: number;
  faab_balance: number;
  external_id: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  league: League;
  user: User;
  draftPicks: DraftPick[];
  homeMatchups: LeagueMatchup[];
  awayMatchups: LeagueMatchup[];
  rosters: LeagueMemberRoster[];
}

export interface LeagueMemberRoster {
  id: number;
  league_member_id: number;
  player_id: number;
  nfl_game_id: number | null;
  season: number;
  week: number;
  lineup_slot_id: number;
  position_rank: number;
  overall_rank: number;
  percent_owned: number;
  percent_started: number;
  percent_changed: number;
  fantasy_points: string;
  espn_projected_points: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  leagueMember: LeagueMember;
  player: Player;
  nflGame: NflGame | null;
}

export interface LeagueMatchup {
  id: number;
  league_id: number;
  home_member_id: number;
  away_member_id: number;
  season: number;
  week: number;
  home_score: number | null;
  away_score: number | null;
  home_projected_score: number | null;
  away_projected_score: number | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  league: League;
  homeTeam: LeagueMember;
  awayTeam: LeagueMember;
}

export interface LeagueSettings {
  id: number;
  league_id: number;
  roster_positions: string[];
  roster_size: number;
  starters_count: number;
  bench_count: number;
  ir_spots: number;
  defense_points_allowed_tiers: any[] | null;
  passing_points_per_yard: string;
  passing_td_points: string;
  interception_points: string;
  rushing_points_per_yard: string;
  rushing_td_points: string;
  receiving_points_per_yard: string;
  receiving_td_points: string;
  reception_points: string;
  fumble_lost_points: string;
  two_point_conversion_points: string;
  field_goal_0_39_points: string;
  field_goal_40_49_points: string;
  field_goal_50_plus_points: string;
  extra_point_points: string;
  defense_sack_points: string;
  defense_interception_points: string;
  defense_fumble_recovery_points: string;
  defense_td_points: string;
  defense_safety_points: string;
  created_at: string;
  updated_at: string;
  league: League;
}

export interface NflGame {
  id: number;
  espn_id: number;
  home_team_id: string;
  away_team_id: string;
  season: number;
  week: number;
  starts_at: string;
  home_score: number;
  away_score: number;
  is_completed: boolean;
  is_playoff: boolean;
  created_at: string;
  updated_at: string;
  homeTeam: Team;
  awayTeam: Team;
  playerProjections: PlayerProjection[];
}

export interface Player {
  id: number;
  espn_id: number | null;
  pfr_id: string | null;
  fp_id: string | null;
  position_id: string;
  team_id: string | null;
  first_name: string;
  last_name: string;
  full_name: string;
  height: string | null;
  weight: string | null;
  college: string | null;
  draft_year: string | null;
  draft_round: string | null;
  draft_pick: string | null;
  draft_team: string | null;
  birth_date: string | null;
  jersey_number: string | null;
  headshot: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  position: Position;
  team?: Team;
  age?: number;
  isRookie?: boolean;
  isFirstRoundPick?: boolean;
  aliases: PlayerAlias[];
  draftPicks: DraftPick[];
  draftRankings: DraftRanking[];
  playerProjections: PlayerProjection[];
  playerTeams: PlayerTeam[];
  projected_points?: number | string;
  actual_points?: number | string;
  game?: NflGame;
}

export interface PlayerAlias {
  id: number;
  player_id: number;
  team_id: number;
  position_id: number;
  name: string;
  alias: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  player: Player;
  team: Team;
  position: Position;
}

export interface PlayerMissing {
  id: number;
  source_class: string | null;
  source_data: string | object;
  unique_id_key: string | null;
  unique_id_value: string | null;
  name: string | null;
  position_id: string | null;
  team_id: string | null;
}

export interface Position {
  id: string;
  name: string;
  abbreviation: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  players: Player[];
}

export interface Team {
  id: string;
  name: string;
  location: string;
  abbreviation: string;
  espn_id: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  fullName?: string;
  playerTeams: PlayerTeam[];
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  createdLeagues: League[];
  leagueMemberships: LeagueMember[];
  leagues: League[];
  drafts: Draft[];
}

export interface PlayerTeam {
  id: number;
  player_id: number;
  team_id: string;
  is_current_team: boolean;
  created_at: string;
  updated_at: string;
  player: Player;
  team: Team;
}
