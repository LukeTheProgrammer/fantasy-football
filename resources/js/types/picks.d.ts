export interface ClockSlot {
  external_id: string | null;
  league_member_id: number | null;
  overall_pick_number: number;
  owner_name: string | null;
  pick_number: number;
  round: number;
  team_name: string | null;
}

export interface RoundSlotPlayer {
  full_name: string | null;
  headshot: string | null;
  pick_id: number;
  player_id: number | null;
  position: string | null;
  team: string | null;
}

export interface RoundSlot extends ClockSlot {
  is_current: boolean;
  is_made: boolean;
  /** Whether the slot was given up rather than used on a player. */
  is_skipped: boolean;
  /** Set once the slot has been used, on a skipped slot as well as a pick. */
  pick_id: number | null;
  /** The player taken in this slot, once it has been used. */
  player: RoundSlotPlayer | null;
}

export interface DraftClock {
  current: ClockSlot | null;
  made: number;
  /** The round the clock is on, 1 based. */
  current_round: number;
  remaining: number;
  /** The draft round by round, so any round can be shown. */
  rounds: RoundSlot[][];
  total: number;
  upcoming: ClockSlot[];
}

export interface RosterPlayer {
  full_name: string | null;
  league_member_id: number;
  pick_id: number | null;
  player_id: number | null;
  position: string | null;
  /** How the team came by him: 'Keeper', or the round and pick he was taken at. */
  source: string;
  team: string | null;
}

export interface RosterSlot {
  index: number;
  is_starter: boolean;
  /** The slot as it reads on a lineup, e.g. 'RB/WR/TE'. */
  label: string;
  player: RosterPlayer | null;
  slot: string;
}

export interface RosterPick {
  full_name: string | null;
  overall_pick_number: number;
  pick_id: number;
  player_id: number | null;
  position: string | null;
  round: number;
  team: string | null;
}

export interface TeamRoster {
  external_id: string;
  league_member_id: number;
  owner_name: string;
  picks: RosterPick[];
  slots: RosterSlot[];
  team_name: string;
}

export interface BoardPlayer {
  /** ESPN's average draft position, as an overall pick number. */
  adp: number | null;
  /** ESPN's average auction value, unadjusted for this league's format. */
  adv: number | null;
  /** FantasyPros' dynasty rank, published in full PPR: an ordering, not a score. */
  dynasty: number | null;
  full_name: string | null;
  /** The draft_rankings row id. */
  id: number;
  /** Projected points above the last starter at his position, per week. */
  par: number | null;
  player_id: number;
  /** Projected points per week. */
  points: number | null;
  position: string | null;
  rank: number | null;
  team: string | null;
  tier: number | null;
}

export interface PlayerSeason {
  games: number;
  interceptions: number;
  passing_tds: number;
  passing_yards: number;
  points: number;
  points_per_game: number | null;
  receiving_tds: number;
  receiving_yards: number;
  receptions: number;
  rushing_carries: number;
  rushing_tds: number;
  rushing_yards: number;
  season: number;
  targets: number;
  team: string | null;
}

export interface PlayerProfile {
  age: number | null;
  full_name: string | null;
  headshot: string | null;
  jersey: number | null;
  owner: { source: string; team_name: string | null } | null;
  player_id: number;
  position: string | null;
  /** Projected points and points above replacement, per week. */
  projection: { par: number | null; points: number } | null;
  ranking: { adp: number | null; adv: number | null; rank: number | null; tier: number | null } | null;
  /** Most recent regular season first. */
  seasons: PlayerSeason[];
  team: string | null;
}
