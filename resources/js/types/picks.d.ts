export interface ClockSlot {
  external_id: string | null;
  league_member_id: number | null;
  overall_pick_number: number;
  owner_name: string | null;
  pick_number: number;
  round: number;
  team_name: string | null;
}

export interface RoundSlot extends ClockSlot {
  is_current: boolean;
  is_made: boolean;
}

export interface DraftClock {
  current: ClockSlot | null;
  made: number;
  remaining: number;
  /** Every slot in the round the clock is on, used and unused. */
  round: RoundSlot[];
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
