export interface LeagueResource {
  id: string;
  name: string;
  season: number;
  week: number;
  slug: string;
  description: string;
  platform: string;
  platform_id: string;
  team_count: number;
  is_public: boolean;
  is_active: boolean;
  is_admin: boolean;
  is_syncing: boolean;

  matchups: Record<string, LeagueMatchupResource[]>;
  members: LeagueMemberResource[];
  creator?: LeagueCreatorResource;
  draft?: LeagueDraftResource;
  settings?: LeagueSettingsResource;
}

export interface LeagueCreatorResource {
  id: string;
  name: string;
  email: string;
}

export interface LeagueMatchupResource {
  id: string;
  season: number;
  week: number;
  /** The platform's bracket this game belongs to; null in the regular season. */
  playoff_tier: string | null;
  /** Which side won, as the platform recorded it: HOME, AWAY, TIE or UNDECIDED. */
  winner: string | null;
  home_score: number;
  away_score: number;
  home_projected_score: number;
  away_projected_score: number;
  home_team: LeagueTeamResource;
  /** A first round bye has no opponent. */
  away_team: LeagueTeamResource | null;
}

export interface LeagueTeamResource {
  id: string;
  team_name: string;
  owner_name: string;
  team_logo: string;
  /** Where the team went into the playoffs; null when it missed them. */
  playoff_seed: number | null;
  /** Where it finished, once the platform has settled the season. */
  final_rank: number | null;
  wins: number;
  losses: number;
  ties: number;
  points_for: number;
  points_against: number;
  faab_balance: number;
}

export interface LeagueMemberResource {
  id: string;
  league_id: string;
  user_id: string;
  external_id: string;
  team_name: string;
  owner_name: string;
  team_logo: string;
  is_admin: boolean;
  is_active: boolean;
  wins: number;
  losses: number;
  ties: number;
  points_for: number;
  points_for_rank: string;
  points_against: number;
  points_against_rank: string;
  /** Where the team went into the playoffs; null when it missed them. */
  playoff_seed: number | null;
  /** Where it finished, once the platform has settled the season. */
  final_rank: number | null;
  faab_balance: number;

  rosters: Record<string, LeagueRosterResource[]>;
}

export interface LeagueRosterResource {
  id: string;
  league_member_id: string;
  player_id: string;
  nfl_game_id: string;
  season: number;
  week: number;
  lineup_slot_id: number;
  lineup_slot_name: string | null;
  position_rank: number;
  overall_rank: number;
  percent_owned: number;
  percent_started: number;
  fantasy_points: number;
  espn_diff: number;
  fp_diff: number;

  nfl_game: NflGameResource;
  player: LeaguePlayerResource;
  player_projection: LeagueProjectionResource;
}

export interface LeaguePlayerResource {
  id: string;
  espn_id: string;
  pfr_id: string;
  fp_id: string;
  first_name: string;
  last_name: string;
  full_name: string;
  position: string;
  team: string;
  jersey: string;
  height: string;
  weight: string;
  college: string;
  bye_week: number;
  fantasy_points: number;
  headshot: string;
}

export interface LeagueProjectionResource {
  id: string;
  player_id: string;
  nfl_game_id: string;
  season: number;
  week: number;
  espn_points: number;
  fp_points: number;
  fp_pos_rank: number;
  fp_pos_rank_min: number;
  fp_pos_rank_max: number;
  fp_pos_rank_avg: number;
  fp_pos_rank_std: number;
}

export interface NflGameResource {
  id: string;
  espn_id: string;
  season: number;
  week: number;
  starts_at: string;
  day: string;
  time: string;
  home_score: number;
  away_score: number;
  is_completed: boolean;
  is_playoff: boolean;
  is_bye: boolean;

  home_team: NflTeamResource;
  away_team: NflTeamResource;
}

export interface NflTeamResource {
  id: string;
  espn_id: string;
  pfr_id: string;
  abbreviation: string;
  location: string;
  name: string;
  logo: string;
  conference: string;
  division: string;
}

export interface LeagueDraftResource {
  id: number;
  league_id: number;
  draft_date: string | null;
  draft_type: string;
  is_completed: boolean;
  auction_budget: number | null;
  current_pick: number | null;
  current_round: number | null;
  time_per_pick: number;
  is_active: boolean;

  picks: LeagueDraftPickResource[];
}

export interface LeagueDraftPickResource {
  id: number;
  draft_id: number;
  round: number;
  pick_number: number;
  overall_pick_number: number;
  amount: string | null;
  is_keeper: boolean;

  league_member: LeagueDraftMemberResource;
  player: LeagueDraftPlayerResource;
}

export interface LeagueDraftMemberResource {
  id: number;
  league_id: number;
  user_id: number | null;
  external_id: string | null;
  team_name: string;
  owner_name: string | null;
  team_logo: string | null;
  is_admin: boolean;
  is_active: boolean;
  wins: number;
  losses: number;
  ties: number;
  points_for: string;
  points_against: string;
  faab_balance: number;
}

export interface LeagueDraftPlayerResource {
  id: number;
  ulid: string;
  espn_id: number | null;
  pfr_id: string | null;
  fp_id: string | null;
  position_id: string;
  team_id: string | null;
  first_name: string;
  last_name: string;
  full_name: string | null;
  jersey_number: number | null;
  height: string | null;
  weight: string | null;
  college: string | null;
  draft_year: string | null;
  draft_round: string | null;
  draft_pick: string | null;
  draft_team: string | null;
  birth_date: string | null;
  headshot: string | null;
}
