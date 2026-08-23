import { Heading } from '@/common/heading/Heading';
import { LeagueForm } from '@/modules/leagues/components/LeagueForm';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import { type League } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head } from '@inertiajs/react';

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
    title: 'Edit Credentials',
    href: '#',
  },
];

interface EditLeagueProps extends PageProps {
  league: League;
}

export default function EditLeague({ league }: EditLeagueProps) {
  // Map League to LeagueFormData (for platform credentials)
  const formData = {
    platform: league.platform || 'espn',
    espn_league_id: league.credentials?.find((cred: any) => cred.key === 'espn_league_id')?.value || '',
    espn_s2: league.credentials?.find((cred: any) => cred.key === 'espn_s2')?.value || '',
    espn_swid: league.credentials?.find((cred: any) => cred.key === 'espn_swid')?.value || '',
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Edit League" />

      <div className="flex-1 p-8">
        <Heading title="Edit League Credentials" description="Update your league's platform connection settings" />

        <LeagueForm
          initialData={formData}
          submitEndpoint={`/api/leagues/${league.id}/credentials`}
          submitMethod="patch"
          submitButtonText="Update"
          processingButtonText="Updating..."
          successMessage="League credentials have been updated successfully!"
          redirectPath={`/leagues/${league.id}`}
          onSuccess={() => true}
        />
      </div>
    </AppLayout>
  );
}
