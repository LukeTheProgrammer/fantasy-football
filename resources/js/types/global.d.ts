import type { route as routeFn, RouteName } from 'ziggy-js';

declare global {
    const route: typeof routeFn;
    interface Window {
        route: typeof routeFn;
    }
    var route: typeof routeFn;
}

declare namespace NodeJS {
    interface Global {
        route: (name: RouteName, params?: Record<string, unknown>, absolute?: boolean) => string;
    }
}

declare namespace Models {
    interface Team {
        id: number;
        espn_id: number | null;
        abbreviation: string;
        location: string;
        name: string;
        conference: string;
        division: string;
        logo: string | null;
        created_at: string;
        updated_at: string;
    }

    interface Player {
        id: number;
        espn_id: number | null;
        first_name: string;
        last_name: string;
        position: string;
        team_id: number;
        created_at: string;
        updated_at: string;
    }
}
