import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { PageProps, type BreadcrumbItem } from '@/types';
import { type Draft, type DraftPick } from '@/types/models';
import { Head, Link } from '@inertiajs/react';

interface DraftResultsProps extends PageProps {
  draft: Draft;
  teamResults: Record<string, DraftPick[]>;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Drafts',
    href: '/drafts',
  },
  {
    title: 'Draft Results',
    href: '#',
  },
];

export default function DraftResults({ draft, teamResults }: DraftResultsProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} ${draft.league.season} Results`} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <Heading title={`${draft.league.name} ${draft.league.season} Draft Results`} />
          <div className="mt-4 flex items-center space-x-2 md:mt-0">
            <Link href={route('drafts.show', draft.id)}>
              <Button variant="outline">Back to Draft</Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
          {Object.entries(teamResults).map(([leagueMemberId, picks]) => (
            <Card key={leagueMemberId}>
              <CardContent>
                <div className="mb-4 grid w-full">
                  <h2 className="text-lg font-semibold">{picks[0]?.leagueMember?.team_name || 'Unknown Team'}</h2>
                  <p className="text-sm text-muted-foreground">
                    {picks[0]?.leagueMember?.owner_name} &middot; {picks.length} picks
                  </p>
                </div>

                {picks.map((pick) => (
                  <div key={pick.id} className="mb-3 flex items-center justify-between gap-2">
                    <div className="min-w-0">
                      <h3 className="text-md truncate font-semibold">
                        {pick.player.first_name} {pick.player.last_name}
                      </h3>
                      <p className="text-xs text-muted-foreground">
                        Round {pick.round}, Pick #{pick.pick_number}
                        {pick.is_keeper && ' · Keeper'}
                      </p>
                    </div>
                    <PositionBadge position={pick.player.position} />
                  </div>
                ))}
              </CardContent>
            </Card>
          ))}
        </div>

        {Object.keys(teamResults).length === 0 && <p className="text-sm text-muted-foreground">No picks have been made in this draft yet.</p>}
      </div>
    </AppLayout>
  );
}
