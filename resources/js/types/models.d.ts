
export interface Draft {
    id: number;
    league_id: number;
    league: League;
    draft_date: string;
    draft_type: string;
    is_completed: boolean;
    auction_budget: number;
    current_pick: number;
    current_round: number;
    time_per_pick: number;
    is_active: boolean;
    picks: DraftPick[];
}

export interface DraftPick {
    id: number;
    draft_id: number;
    draft: Draft;
    pick_number: number;
    round: number;
    amount: number;
    is_keeper: boolean;
    previous_year_cost: number;
    pick_time: string;
    player: Player;
    leagueMember: LeagueMember;
}

export interface League {
    id: number;
    created_by_user_id: number;
    creator: User;
    name: string;
    year: number;
    slug: string;
    description: string | null;
    team_count: number;
    join_code: string;
    is_public: boolean;
    is_active: boolean;
    draft: Draft;
    members: LeagueMember[];
    settings: LeagueSettings;
}

export interface LeagueMember {
    id: number;
    league_id: number;
    league: League;
    user_id: number;
    user: User;
    team_name: string;
    team_logo: string | null;
    draft_position: number | null;
    is_admin: boolean;
    is_active: boolean;
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

export interface Player {
    id: number;
    espn_id: number | null;
    position_id: number;
    first_name: string;
    last_name: string;
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
}

export interface Position {
    id: number;
    name: string;
}

export interface Team {
    id: number;
    name: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    deleted_at: string | null;
}
