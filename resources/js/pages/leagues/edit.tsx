import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import LeagueForm from './form';
import { Head } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Leagues',
    href: '/leagues',
  },
  {
    title: 'Edit League',
    href: '#',
  },
];

// interface LeagueMember {
//   id: number;
//   league_id: number;
//   user_id: number;
//   team_name: string;
//   team_logo: string | null;
//   draft_position: number | null;
//   is_admin: boolean;
//   is_active: boolean;
//   created_at: string;
//   updated_at: string;
//   user: {
//     id: number;
//     name: string;
//     email: string;
//   };
// }

// interface LeagueSettings {
//   id: number;
//   league_id: number;
//   roster_positions: string[];
//   roster_size: number;
//   starters_count: number;
//   bench_count: number;
//   ir_spots: number;
//   passing_points_per_yard?: number;
//   passing_yards_per_point?: number;
//   passing_td_points: number;
//   interception_points: number;
//   rushing_points_per_yard?: number;
//   rushing_yards_per_point?: number;
//   rushing_td_points: number;
//   receiving_points_per_yard?: number;
//   receiving_yards_per_point?: number;
//   receiving_td_points: number;
//   reception_points: number;
//   fumble_lost_points: number;
//   two_point_conversion_points: number;
//   field_goal_0_39_points: number;
//   field_goal_40_49_points: number;
//   field_goal_50_plus_points: number;
//   extra_point_points: number;
//   defense_sack_points: number;
//   defense_interception_points: number;
//   defense_fumble_recovery_points: number;
//   defense_td_points: number;
//   defense_safety_points: number;
//   defense_points_allowed_tiers: Record<string, number>;
//   created_at?: string;
//   updated_at?: string;
// }

interface League {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  team_count?: number;
  max_teams?: number;
  is_public: boolean;
  draft_type: string;
  draft_date: string | null;
  join_code: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  creator?: {
    id: number;
    name: string;
  };
  settings: LeagueSettings;
  members?: LeagueMember[];
  user_is_admin?: boolean;
  user_is_member?: boolean;
}

interface EditLeagueProps extends PageProps {
  league: League;
}

export default function EditLeague({ league }: EditLeagueProps) {
  // Map League to LeagueFormData
  const formData = {
    name: league.name,
    description: league.description || '',
    team_count: league.team_count || 10,
    is_public: league.is_public,
    draft_type: league.draft_type,
    draft_date: league.draft_date || '',
    settings: {
      roster_positions: league.settings.roster_positions,
      roster_size: league.settings.roster_size,
      starters_count: league.settings.starters_count,
      bench_count: league.settings.bench_count,
      ir_spots: league.settings.ir_spots,
      passing_points_per_yard: league.settings.passing_points_per_yard || 0,
      passing_td_points: league.settings.passing_td_points,
      interception_points: league.settings.interception_points,
      rushing_points_per_yard: league.settings.rushing_points_per_yard || 0,
      rushing_td_points: league.settings.rushing_td_points,
      receiving_points_per_yard: league.settings.receiving_points_per_yard || 0,
      receiving_td_points: league.settings.receiving_td_points,
      reception_points: league.settings.reception_points,
      fumble_lost_points: league.settings.fumble_lost_points,
      two_point_conversion_points: league.settings.two_point_conversion_points,
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Edit League" />

      <div className="flex-1 p-8">
        <Heading title="Edit Fantasy League" description="Update your fantasy football league settings" />

        <LeagueForm
          initialData={formData}
          submitEndpoint={`/api/leagues/${league.id}`}
          submitMethod="patch"
          submitButtonText="Update League"
          processingButtonText="Updating..."
          successMessage="Your fantasy league has been updated successfully!"
          redirectPath={`/leagues/${league.id}`}
          onSuccess={() => true}
        />
      </div>
    </AppLayout>
  );
}
