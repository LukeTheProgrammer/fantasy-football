import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import LeagueForm from './form';
import { Head } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { type BreadcrumbItem } from '@/types';
import { type League } from '@/types/models';

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
