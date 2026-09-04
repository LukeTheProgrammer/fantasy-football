export interface ClockSlot {
  overall_pick_number: number;
  round: number;
  pick_number: number;
  league_member_id: number | null;
  external_id: string | null;
  team_name: string | null;
  owner_name: string | null;
}

export interface DraftClock {
  made: number;
  total: number;
  remaining: number;
  current: ClockSlot | null;
  upcoming: ClockSlot[];
}

export interface RosterPlayer {
  player_id: number | null;
  full_name: string | null;
  position: string | null;
  team: string | null;
}

export interface RosterPick extends RosterPlayer {
  pick_id: number;
  round: number;
  overall_pick_number: number;
}

export interface TeamRoster {
  league_member_id: number;
  external_id: string;
  team_name: string;
  owner_name: string;
  keepers: RosterPlayer[];
  picks: RosterPick[];
}
