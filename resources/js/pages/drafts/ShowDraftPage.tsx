import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BudgetDialog } from '@/modules/drafts/components/BudgetDialog';
import { PositionPriceChart } from '@/modules/drafts/components/PositionPriceChart';
import { TeamRosters } from '@/modules/drafts/components/TeamRosters';
import { isUserDraftAdmin } from '@/modules/drafts/helpers/isUserDraftAdmin';
import { SeasonSelect } from '@/modules/leagues/components/SeasonSelect';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { PageProps, type BreadcrumbItem, type SharedData } from '@/types';
import { type AuctionBudget, type Draft, type RosterSlot, type SeasonOption } from '@/types/models';
import { Head, Link, usePage } from '@inertiajs/react';

interface DraftShowProps extends PageProps {
  draft: Draft;
  seasons: SeasonOption[];
  /** Roster slots keyed by league member id. */
  rosters: Record<number, RosterSlot[]>;
  budget: AuctionBudget | null;
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

export default function ShowDraft({ draft, seasons, rosters, budget }: DraftShowProps) {
  const { auth } = usePage<SharedData>().props;
  const userId = auth.user.id;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} ${draft.league.season}`} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <div>
            <Heading title={`${draft.league.name} ${draft.league.season}`} />
          </div>
          <div className="mt-4 flex items-center space-x-2 md:mt-0">
            {!draft.is_completed && budget && <BudgetDialog budget={budget} draftId={draft.id} />}
            {!draft.is_completed && isUserDraftAdmin(draft, userId) && (
              <Link href={route('drafts.edit', draft.id)}>
                <Button variant="outline">Edit Draft</Button>
              </Link>
            )}
            <SeasonSelect
              seasons={seasons}
              season={draft.league.season}
              routeName="drafts.show"
              routeParams={(option) => [option.id, option.season]}
            />
          </div>
        </div>

        <Card className="mb-8">
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between gap-6">
              <div className="flex items-center justify-start gap-24">
                <div>
                  <p className="text-sm text-muted-foreground">Draft Type</p>
                  <h2 className="text-lg font-semibold">{draft.draft_type === 'auction' ? 'Auction' : 'Snake'}</h2>
                </div>

                <div>
                  <p className="text-sm text-muted-foreground">Draft Date</p>
                  <h2 className="text-lg font-semibold">{new Date(draft.draft_date).toLocaleDateString()}</h2>
                </div>

                <div>
                  <p className="text-sm text-muted-foreground">Draft Complete</p>
                  <h2 className="text-lg font-semibold">{draft.is_completed ? 'Y' : 'N'}</h2>
                </div>
              </div>
              <div className="flex items-center justify-end">
                {!draft.is_completed && (
                  <Link href={route('drafts.draft-room', draft.id)}>
                    <Button>Draft Room</Button>
                  </Link>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        {draft.is_completed && draft.draft_type === 'auction' && (
          <div className="mb-8">
            <Card>
              <CardHeader>
                <CardTitle>Prices by Position</CardTitle>
                <CardDescription>What each position cost, most expensive first.</CardDescription>
              </CardHeader>
              <CardContent>
                <PositionPriceChart picks={draft.picks} />
              </CardContent>
            </Card>
          </div>
        )}

        <TeamRosters members={draft.league.members} rosters={rosters} isAuction={draft.draft_type === 'auction'} />
      </div>
    </AppLayout>
  );
}
