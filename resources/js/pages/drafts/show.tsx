import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { isUserDraftAdmin } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft, type DraftPick } from '@/types/models';

interface DraftShowProps extends PageProps {
  draft: Draft;
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
    title: 'Draft Details',
    href: '#',
  },
];

export default function ShowDraft({ draft }: DraftShowProps) {
  const { auth } = usePage<SharedData>().props;
  const userId = auth.user.id;

  const draftPicksByRound = draft.picks.reduce((acc, draftPick) => {
    if (!acc[draftPick.round]) {
      acc[draftPick.round] = [];
    }
    acc[draftPick.round].push(draftPick);
    return acc;
  }, {} as Record<number, DraftPick[]>);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} ${draft.league.season}`} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <div>
            <Heading title={`${draft.league.name} ${draft.league.season}`} />
          </div>
          <div className="mt-4 flex space-x-2 md:mt-0">
            {isUserDraftAdmin(draft, userId) && (
              <Link href={route('drafts.edit', draft.id)}>
                <Button variant="outline">Edit Draft</Button>
              </Link>
            )}
            <Link href={route('drafts.index')}>
              <Button variant="outline">Back to Drafts</Button>
            </Link>
          </div>
        </div>

        <div className="mb-8 grid grid-cols-1 gap-6">
          {/* League Details Card */}
          <Card>
            <CardContent className="space-y-4">
              <div className="mb-8 grid w-full">
                <h2 className="text-lg font-semibold">{draft.league.name} {draft.league.season} Draft</h2>
                <p className="text-sm text-muted-foreground">Information about your fantasy football draft.</p>
              </div>

              <div className="mb-8 grid w-full">
                <p className="text-sm text-muted-foreground">Draft Type</p>
                <h2 className="text-lg font-semibold">{draft.draft_type}</h2>
              </div>

              <div className="mb-8 grid w-full">
                <p className="text-sm text-muted-foreground">Draft Date</p>
                <h2 className="text-lg font-semibold">{draft.draft_date}</h2>
              </div>

              <div className="mb-8 grid w-full">
                <p className="text-sm text-muted-foreground">Draft Complete</p>
                <h2 className="text-lg font-semibold">{draft.is_completed}</h2>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="mb-8 grid grid-cols-5 gap-6">
          {Object.entries(draftPicksByRound).map(([round, draftPicks]) => (
            <Card key={round}>
              <CardContent className="">
                <div className="mb-8 grid w-full">
                  <h2 className="text-lg font-semibold">Round {round}</h2>
                  <p className="text-sm text-muted-foreground">{draftPicks.length} Picks</p>
                </div>

                {draftPicks.map((pick) => (
                  <div key={pick.id} className="mb-4 grid w-full">
                    <p className="text-sm text-muted-foreground">Pick #{pick.pick_number}</p>
                    <h3 className="text-md font-semibold">
                      {pick.player ? `${pick.player.first_name}  ${pick.player.last_name}` : 'Not selected'}
                    </h3>
                    <p className="text-xs text-muted-foreground">
                      {pick.leagueMember?.team_name || 'Unknown Team'}
                    </p>
                  </div>
                ))}
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </AppLayout>
  );
}
