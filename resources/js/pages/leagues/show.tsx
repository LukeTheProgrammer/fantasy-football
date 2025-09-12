import AppLayout from '@/layouts/app-layout';
import DraftTab from '@/components/leagues/tab-content/draft-tab';
import Heading from '@/components/heading';
import LeagueTab from '@/components/leagues/tab-content/league-tab';
import SettingsTab from '@/components/leagues/tab-content/settings-tab';
import TeamsTab from '@/components/leagues/tab-content/teams-tab';
import { Button } from '@/components/ui/button';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { isUserLeagueAdmin } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
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
    title: 'League Details',
    href: '#',
  },
];

export default function ShowLeague({ league }: PageProps & { league: League }) {
  const { auth } = usePage<SharedData>().props;

  const userId = auth.user.id;
  const userIsAdmin = isUserLeagueAdmin(league, userId);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={league.name} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row">
          <div>
            <Heading
              title={league.name}
              description={`${league.platform} • ${league.team_count} teams`}
            />
          </div>
          <div className="mt-4 flex space-x-2 md:mt-0">
            {userIsAdmin && (
              <Link href={route('leagues.edit', league.id)}>
                <Button variant="outline">Edit League</Button>
              </Link>
            )}
            <Link href={route('leagues.index')}>
              <Button variant="outline">Back to Leagues</Button>
            </Link>
          </div>
        </div>

        <div className="mb-8">
          <Tabs defaultValue="league">
            <TabsList className="mb-6">
              <TabsTrigger className="w-[7rem]" value="league">League</TabsTrigger>
              <TabsTrigger className="w-[7rem]" value="draft">Draft</TabsTrigger>
              <TabsTrigger className="w-[7rem]" value="teams">Teams</TabsTrigger>
              <TabsTrigger className="w-[7rem]" value="standings">Standings</TabsTrigger>
              <TabsTrigger className="w-[7rem]" value="settings">Settings</TabsTrigger>
            </TabsList>
            <TabsContent value="league">
              <LeagueTab league={league} />
            </TabsContent>
            <TabsContent value="draft">
              <DraftTab league={league} />
            </TabsContent>
            <TabsContent value="teams">
              <TeamsTab league={league} />
            </TabsContent>
            <TabsContent value="standings">Standings Overview</TabsContent>
            <TabsContent value="settings">
              <SettingsTab league={league} />
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </AppLayout>
  );
}
