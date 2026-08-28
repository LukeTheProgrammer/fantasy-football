import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader } from '@/components/ui/card';
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
    <Card>
      <CardHeader>
        <CardDescription>
          Applying a plan saves it as your budget. Nothing else changes: you can still edit it slot by slot afterwards.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
          {suggestions.map((suggestion) => (
            <div key={suggestion.focus} className="flex flex-col rounded-md border">
              <div className="border-b px-3 py-2">
                <p className="font-medium">{suggestion.label}</p>
                <p className="text-xs text-muted-foreground tabular-nums">
                  {money(suggestion.starters)} on starters · {money(suggestion.unplanned)} unplanned
                </p>
              </div>

              <div className="flex-1 space-y-1 px-3 py-2">
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

              <div className="border-t px-3 py-2">
                <Button variant="outline" size="sm" className={cn('w-full')} disabled={applying !== null} onClick={() => apply(suggestion)}>
                  {applying === suggestion.focus ? 'Applying...' : 'Use this plan'}
                </Button>
              </div>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
