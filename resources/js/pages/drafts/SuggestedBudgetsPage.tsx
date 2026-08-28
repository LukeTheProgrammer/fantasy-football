import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { SuggestedBudgets } from '@/modules/drafts/components/SuggestedBudgets';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { PageProps, type BreadcrumbItem } from '@/types';
import { type AuctionBudget, type BudgetSuggestion, type Draft } from '@/types/models';
import { Head, Link } from '@inertiajs/react';
import { BudgetDialog } from '@/modules/drafts/components/BudgetDialog';

interface SuggestedBudgetsProps extends PageProps {
  draft: Draft;
  budget: AuctionBudget;
  suggestions: BudgetSuggestion[];
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
    title: 'Suggested Budgets',
    href: '#',
  },
];

export default function SuggestedBudgetsPage({ draft, budget, suggestions }: SuggestedBudgetsProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} Suggested Budgets`} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <Heading title="Suggested Budgets" description={`Three ways to spend your $${budget.budget}, each built around a different position.`} />
          <div className="mt-4 flex items-center justify-end space-x-2 md:mt-0">
            {!draft.is_completed && budget && <BudgetDialog budget={budget} draftId={draft.id} />}
            <Link href={route('drafts.show', [draft.league_id, draft.league.season])}>
              <Button variant="outline">Back to Draft</Button>
            </Link>
            {!draft.is_completed && (
              <Link href={route('drafts.draft-room', draft.id)}>
                <Button>Draft Room</Button>
              </Link>
            )}
          </div>
        </div>

        <SuggestedBudgets suggestions={suggestions} draftId={draft.id} />
      </div>
    </AppLayout>
  );
}
