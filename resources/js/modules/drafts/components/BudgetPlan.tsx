import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { money } from '@/modules/drafts/helpers/money';
import { type AuctionBudget, type BudgetRow } from '@/types/models';
import { useForm } from '@inertiajs/react';
import { useMemo } from 'react';

interface BudgetPlanProps {
  budget: AuctionBudget;
  draftId: number;
}

/**
 * An over or under, coloured only when the slot went over its plan.
 */
function difference(value: number | null) {
  if (value === null) {
    return <span className="text-muted-foreground">—</span>;
  }

  return <span className={cn(value < 0 && 'text-destructive')}>{value < 0 ? `-${money(Math.abs(value))}` : money(value)}</span>;
}

/**
 * A plan for the auction, slot by slot, against what has actually been spent.
 *
 * The plan is never adjusted automatically. An overspent slot shows as a
 * negative difference and what to do about it stays your call.
 */
export function BudgetPlan({ budget, draftId }: BudgetPlanProps) {
  const { data, setData, put, processing, isDirty } = useForm<{ allocations: Record<string, string> }>({
    allocations: Object.fromEntries(budget.rows.map((row) => [row.key, row.planned !== null ? String(row.planned) : ''])),
  });

  // Totals follow what is typed rather than what is saved, so the plan adds up
  // while it is being written.
  const planned = useMemo(() => Object.values(data.allocations).reduce((total, amount) => total + (Number(amount) || 0), 0), [data.allocations]);

  const unplanned = budget.budget - planned;

  // The difference follows the box rather than the saved plan, so a number
  // typed mid auction is measured against what was spent immediately.
  const differenceFor = (row: BudgetRow) => {
    const typed = data.allocations[row.key];

    if (typed === '' || typed === undefined || row.actual === null) {
      return null;
    }

    return (Number(typed) || 0) - row.actual;
  };

  const handleSave = () => {
    put(route('drafts.budget.update', draftId), { preserveScroll: true });
  };

  return (
    <Card className="flex h-full min-h-0 flex-col overflow-hidden">
      <CardHeader className="py-0">
        <CardTitle>
          <div className="flex items-center justify-between gap-2">
            <div className="min-w-0">
              <p className="truncate text-base">Budget</p>
              <p className="text-xs font-normal text-muted-foreground tabular-nums">
                {money(planned)} planned · {unplanned >= 0 ? `${money(unplanned)} unplanned` : `${money(Math.abs(unplanned))} over`}
              </p>
            </div>
            <Button size="sm" onClick={handleSave} disabled={processing || !isDirty}>
              {processing ? 'Saving...' : 'Save'}
            </Button>
          </div>
        </CardTitle>
      </CardHeader>
      <CardContent className="min-h-0 flex-1 overflow-hidden py-0">
        <Table className="table-fixed" containerClassName="h-full overflow-auto">
          {/* Cells carry the background too: a background on thead alone does
              not paint over rows scrolling beneath a sticky header. */}
          <TableHeader className="sticky top-0 z-10 bg-card shadow-sm [&_th]:bg-card">
            <TableRow>
              <TableHead className="w-[30%]">Slot</TableHead>
              <TableHead className="w-[26%] text-center">Plan</TableHead>
              <TableHead className="w-[22%] text-center">Actual</TableHead>
              <TableHead className="w-[22%] text-center">Diff</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {budget.rows.map((row) => (
              <TableRow key={row.key}>
                <TableCell className="truncate text-xs font-medium">
                  {row.label}
                  {row.filled_by && <span className="block truncate text-[10px] font-normal text-muted-foreground">{row.filled_by}</span>}
                </TableCell>
                <TableCell className="px-1">
                  <Input
                    type="number"
                    min={0}
                    inputMode="numeric"
                    placeholder="$"
                    className="h-8 text-center tabular-nums"
                    value={data.allocations[row.key] ?? ''}
                    onChange={(event) => setData('allocations', { ...data.allocations, [row.key]: event.target.value })}
                  />
                </TableCell>
                <TableCell className="text-center tabular-nums">{row.actual !== null ? money(row.actual) : '—'}</TableCell>
                <TableCell className="text-center tabular-nums">{difference(differenceFor(row))}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}
