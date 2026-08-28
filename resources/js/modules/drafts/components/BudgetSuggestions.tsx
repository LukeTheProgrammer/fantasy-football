import { cn } from '@/common/helpers/cn';
import { money } from '@/modules/drafts/helpers/money';
import { type BudgetSuggestion } from '@/types/models';

interface BudgetSuggestionsProps {
  suggestions: BudgetSuggestion[];
  onApply: (suggestion: BudgetSuggestion) => void;
  /** The plan currently in the form, so an applied suggestion reads as chosen. */
  applied: string | null;
}

/**
 * Three ways to spend the budget, each built around a different position.
 *
 * The plans are a starting point rather than an answer: applying one fills the
 * boxes in and nothing is saved until the plan is saved, so a suggestion can be
 * taken apart before it is committed to.
 */
export function BudgetSuggestions({ suggestions, onApply, applied }: BudgetSuggestionsProps) {
  if (suggestions.length === 0) {
    return null;
  }

  return (
    <div className="grid grid-cols-3 gap-2">
      {suggestions.map((suggestion) => (
        <button
          key={suggestion.focus}
          type="button"
          onClick={() => onApply(suggestion)}
          className={cn(
            'rounded-md border p-2 text-left transition-colors hover:bg-muted/60',
            applied === suggestion.focus && 'bg-primary/15 hover:bg-primary/15',
          )}
        >
          <p className="text-sm font-medium">{suggestion.label}</p>
          <p className="text-xs text-muted-foreground tabular-nums">{money(suggestion.starters)} on starters</p>
          <p className="mt-1 truncate text-xs text-muted-foreground">{headline(suggestion)}</p>
        </button>
      ))}
    </div>
  );
}

/**
 * The most expensive player the plan expects to buy, which is the thing that
 * separates one plan from another.
 */
function headline(suggestion: BudgetSuggestion): string {
  const best = Object.entries(suggestion.allocations)
    .filter(([key]) => suggestion.players[key])
    .sort(([, a], [, b]) => b - a)
    .at(0);

  if (!best) {
    return 'No players priced yet';
  }

  const [key, amount] = best;

  return `${suggestion.players[key]?.full_name} ${money(amount)}`;
}
