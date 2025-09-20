export interface Draft {
  id: number;
  league_id: number;
  draft_date: string;
  draft_type: string;
  is_completed: boolean;
  auction_budget: number|null;
  current_pick: number|null;
  current_round: number|null;
  time_per_pick: number;
  is_active: boolean;
  league: League;
  picks: DraftPick[];
}

export interface DraftPick {
  id: number;
  draft_id: number;
  draft: Draft;
  league_member_id: number;
  leagueMember: LeagueMember;
  player_id: number;
  player: Player;
  pick_number: number;
  round: number;
  amount: number;
  is_keeper: boolean;
  previous_year_cost: number;
  pick_time: string;
}

export interface DraftRanking {
  id: number;
  player_id: number;
  year: number;
  ranked_at: string;
  type: string;
  source: string | null;
  ppr: number;
  rank: number | null;
  tier: number | null;
  adp: number | null;
  adv: number | null;
  notes: string | null;
  player: Player;
}

export interface FantasyPointsWeek {
  id: number;
  nfl_game_id: number;
  league_id: number;
  player_id: number;
  lineup_slot_id: number;
  espn_projected_points: number;
  points: number;
  position_rank: number;
  overall_rank: number;
  percent_owned: number;
  percent_started: number;
  percent_changed: number;
  nfl_game: NflGame;
  league: League;
  player: Player;
}

export interface League {
  id: number;
  created_by_user_id: number;
  creator: User;
  name: string;
  year: number;
  slug: string;
  description: string | null;
  platform: string;
  team_count: number;
  join_code: string;
  is_public: boolean;
  is_active: boolean;
  draft: Draft;
  fantasy_points_weeks: FantasyPointsWeek[];
  matchups: LeagueMatchup[];
  members: LeagueMember[];
  rosters: LeagueMemberRoster[];
  settings: LeagueSettings;
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
  wins: number,
  losses: number,
  ties: number,
  points_for: number,
  points_against: number,
  faab_balance: number,
  user: User;
  league: League;
  matchups?: LeagueMatchup[];
  rosters?: LeagueMemberRoster[];
}

export interface LeagueMemberRoster {
  id: number;
  league_member_id: number;
  league_member: LeagueMember;
  player_id: number;
  position_rank: number;
  overall_rank: number;
  fantasy_points: number;
  player: Player;
  added_at: string;
  dropped_at: string | null;
}

export interface LeagueMatchup {
  id: number;
  league_id: number;
  home_member_id: number;
  away_member_id: number;
  year: number;
  week: number;
  home_score: number | null;
  away_score: number | null;
  home_projected_score: number | null;
  away_projected_score: number | null;
  league: League;
  home_team: LeagueMember;
  away_team: LeagueMember;
}

export interface LeagueSettings {
  id: number;
  league_id: number;
  roster_positions: string[];
  roster_size: number;
  starters_count: number;
  bench_count: number;
  ir_spots: number;
  passing_points_per_yard: number;
  passing_td_points: number;
  interception_points: number;
  rushing_points_per_yard: number;
  rushing_td_points: number;
  receiving_points_per_yard: number;
  receiving_td_points: number;
  reception_points: number;
  fumble_lost_points: number;
  two_point_conversion_points: number;
}

export interface NflGame {
  id: number;
  espn_id: number;
  home_team_id: number;
  away_team_id: number;
  year: number;
  week: number;
  start_time: string;
  home_score: number;
  away_score: number;
  is_completed: boolean;
  is_playoff: boolean;
  home_team: Team;
  away_team: Team;
}

export interface Player {
  id: number;
  espn_id: number | null;
  team_id: number;
  position_id: number;
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
  headshot: string | null;
  deleted_at: string | null;
  position: Position;
  team: Team;
  projected_points?: number | string;
  actual_points?: number | string;
  game?: NflGame;
}

export interface PlayerAlias {
  id: number;
  player_id: number;
  alias: string;
  deleted_at: string | null;
  player: Player;
}

export interface Position {
  id: number;
  name: string;
  abbreviation: string;
}

export interface Team {
  id: number;
  name: string;
  abbreviation: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  deleted_at: string | null;
}
