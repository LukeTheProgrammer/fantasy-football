import { Heading } from '@/common/heading/Heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { getLeagueUserMember } from '@/modules/leagues/helpers/getLeagueUserMember';
import { isUserLeagueAdmin } from '@/modules/leagues/helpers/isUserLeagueAdmin';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type League } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Leagues',
    href: '/leagues',
  },
];

interface LeagueIndexProps extends PageProps {
  leagues: League[];
}

export default function Leagues({ leagues }: LeagueIndexProps) {
  const { auth } = usePage<SharedData>().props;
  const userId = auth.user.id;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="My Leagues" />

      <div className="flex-1 p-8">
        <Heading
          title="My Fantasy Leagues"
          description="Manage and track your fantasy football leagues"
          rightContent={
            <Link href="/leagues/create">
              <Button size="lg" variant="secondary" className="cursor-pointer">
                <span className="flex justify-between">
                  <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                  <span className="pl-1">Create New League</span>
                </span>
              </Button>
            </Link>
          }
        />

        {leagues.length === 0 ? (
          <div className="mb-8 rounded-lg border bg-card">
            <div className="border-b p-6 py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">You haven't joined any leagues yet</h3>
              <p className="mb-6 text-gray-500 dark:text-gray-400">Create your first league to get started</p>
              <Link href={route('leagues.create')}>
                <Button>Create New League</Button>
              </Link>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {leagues.map((league) => (
              <Card key={league.id} className="overflow-hidden">
                <CardHeader>
                  <CardTitle>{league.name}</CardTitle>
                  <CardDescription>
                    {isUserLeagueAdmin(league, userId) && (
                      <Badge variant="outline" className="mr-2">
                        Admin
                      </Badge>
                    )}
                    {league?.draft?.draft_type === 'auction' ? 'Auction Draft' : 'Snake Draft'}
                    {league.seasons && league.seasons.length > 1 && <span className="ml-2">• {league.seasons.length} seasons</span>}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="mb-2 text-sm text-gray-500 dark:text-gray-400">{league.description || 'No description provided'}</p>
                  <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <span>Your team: {getLeagueUserMember(league, userId)?.team_name}</span>
                    <span className="mx-2">•</span>
                    <span>
                      {league.members?.length || 'N/A'} / {league.team_count} teams
                    </span>
                    <span className="mx-2">•</span>
                    <span>{league.season} season</span>
                  </div>
                </CardContent>
                <CardFooter>
                  <Link href={route('leagues.show', league.id)} className="w-full">
                    <Button variant="outline" className="w-full">
                      View League
                    </Button>
                  </Link>
                </CardFooter>
              </Card>
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}
