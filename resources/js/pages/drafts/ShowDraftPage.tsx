import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BudgetDialog } from '@/modules/drafts/components/BudgetDialog';
import { BudgetPlan } from '@/modules/drafts/components/BudgetPlan';
import { PositionPriceChart } from '@/modules/drafts/components/PositionPriceChart';
import { isUserDraftAdmin } from '@/modules/drafts/helpers/isUserDraftAdmin';
import { SeasonSelect } from '@/modules/leagues/components/SeasonSelect';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { PageProps, type BreadcrumbItem, type SharedData } from '@/types';
import { type AuctionBudget, type Draft, type DraftPick, type SeasonOption } from '@/types/models';
import { Head, Link, usePage } from '@inertiajs/react';

interface DraftShowProps extends PageProps {
  draft: Draft;
  seasons: SeasonOption[];
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

export default function ShowDraft({ draft, seasons, budget }: DraftShowProps) {
  const { auth } = usePage<SharedData>().props;
  const userId = auth.user.id;

  const draftPicksByRound = draft.picks.reduce(
    (acc, draftPick) => {
      if (!acc[draftPick.round]) {
        acc[draftPick.round] = [];
      }
      acc[draftPick.round].push(draftPick);
      return acc;
    },
    {} as Record<number, DraftPick[]>,
  );

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} ${draft.league.season}`} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <div>
            <Heading title={`${draft.league.name} ${draft.league.season}`} />
          </div>
          <div className="mt-4 flex items-center space-x-2 md:mt-0">
            <SeasonSelect
              seasons={seasons}
              season={draft.league.season}
              routeName="drafts.show"
              routeParams={(option) => [option.id, option.season]}
            />
            {!draft.is_completed && budget && <BudgetDialog budget={budget} draftId={draft.id} />}
            {!draft.is_completed && isUserDraftAdmin(draft, userId) && (
              <Link href={route('drafts.edit', draft.id)}>
                <Button variant="outline">Edit Draft</Button>
              </Link>
            )}
          </div>
        </div>

        <div className="mb-8 grid grid-cols-2 gap-6">
          {/* League Details Card */}
          <Card>
            <CardContent className="space-y-4">
              <div className="mb-8 flex w-full items-center justify-between">
                <div>
                  <h2 className="text-lg font-semibold">
                    {draft.league.name} {draft.league.season} Draft
                  </h2>
                  <p className="text-sm text-muted-foreground">Information about your fantasy football draft.</p>
                </div>
                <Link href={route('drafts.draft-room', draft.id)}>
                  <Button>Draft Room</Button>
                </Link>
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

          {budget && <BudgetPlan budget={budget} draftId={draft.id} canEdit={!draft.is_completed} />}
        </div>

        {draft.is_completed && (
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
                    <h3 className="text-md font-semibold">{pick.player ? `${pick.player.first_name}  ${pick.player.last_name}` : 'Not selected'}</h3>
                    <p className="text-xs text-muted-foreground">{pick.leagueMember?.team_name || 'Unknown Team'}</p>
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
