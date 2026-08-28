import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { money } from '@/modules/drafts/helpers/money';
import { type BudgetSuggestion } from '@/types/models';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface SuggestedBudgetsProps {
  suggestions: BudgetSuggestion[];
  draftId: number;
}

/**
 * Three ways to spend the budget, side by side.
 *
 * Each plan names who it expects to buy in each slot, because the number on its
 * own does not say whether $44 at running back is the best one or the fourth
 * best. Applying a plan saves it as the budget, which the plan table then shows
 * against what actually gets spent.
 */
export function SuggestedBudgets({ suggestions, draftId }: SuggestedBudgetsProps) {
  const [applying, setApplying] = useState<string | null>(null);

  if (suggestions.length === 0) {
    return null;
  }

  const apply = (suggestion: BudgetSuggestion) => {
    setApplying(suggestion.focus);

    router.put(
      route('drafts.budget.update', draftId),
      { allocations: suggestion.allocations },
      { preserveScroll: true, onFinish: () => setApplying(null) },
    );
  };

  return (
    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
      {suggestions.map((suggestion) => (
        <BudgetPlan suggestion={suggestion} applying={applying === suggestion.focus} onApply={() => apply(suggestion)} />
      ))}
    </div>
  );
}

function BudgetPlan({ suggestion, applying, onApply }: { suggestion: BudgetSuggestion; applying: boolean; onApply: () => void }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{suggestion.label}</CardTitle>
        <CardDescription>
          {money(suggestion.starters)} on starters · {money(suggestion.unplanned)} unplanned
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="flex-1 space-y-3 px-3 py-2">
          {Object.entries(suggestion.allocations).map(([key, amount]) => {
            const player = suggestion.players[key];

            // The bench is a dollar a spot by design, so listing it says
            // nothing the plan does not already say once.
            if (!player) {
              return null;
            }

            return (
              <div key={key} className="flex items-center gap-2 text-sm">
                <span className="w-16 shrink-0 text-xs font-medium text-muted-foreground">{key}</span>
                <span className="min-w-0 flex-1 truncate">{player.full_name}</span>
                <span className="shrink-0 tabular-nums">{money(amount)}</span>
              </div>
            );
          })}
        </div>
      </CardContent>
      <CardFooter>
        <Button className="w-full" size="lg" disabled={applying} onClick={onApply}>
          {applying ? 'Applying...' : 'Use this plan'}
        </Button>
      </CardFooter>
    </Card>
  );
}
