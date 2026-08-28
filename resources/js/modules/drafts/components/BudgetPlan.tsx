import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { BudgetDialog } from '@/modules/drafts/components/BudgetDialog';
import { money } from '@/modules/drafts/helpers/money';
import { type AuctionBudget, type BudgetRow } from '@/types/models';
import { useMemo } from 'react';

interface BudgetPlanProps {
  budget: AuctionBudget;
  draftId: number;
  canEdit?: boolean;
  className?: string;
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
 * negative difference and what to do about it stays your call. Editing happens
 * in the dialog behind the Edit button.
 */
export function BudgetPlan({ budget, draftId, canEdit = true, className }: BudgetPlanProps) {
  const planned = useMemo(() => budget.rows.reduce((total, row) => total + (row.planned ?? 0), 0), [budget.rows]);
  const spent = useMemo(() => budget.rows.reduce((total, row) => total + (row.actual ?? 0), 0), [budget.rows]);

  const unplanned = budget.budget - planned;

  const differenceFor = (row: BudgetRow) => {
    if (row.planned === null || row.actual === null) {
      return null;
    }

    return row.planned - row.actual;
  };

  return (
    <Card className={cn('flex h-full min-h-0 flex-col overflow-hidden', className)}>
      <CardHeader className="py-0">
        <CardTitle>
          <div className="flex items-center justify-between gap-2">
            <div className="min-w-0">
              <p className="truncate text-base">Budget</p>
              <p className="text-xs font-normal text-muted-foreground tabular-nums">
                {money(planned)} planned · {unplanned >= 0 ? `${money(unplanned)} unplanned` : `${money(Math.abs(unplanned))} over`}
              </p>
            </div>
            {canEdit && <BudgetDialog budget={budget} draftId={draftId} trigger={<Button size="sm">Edit</Button>} />}
          </div>
        </CardTitle>
      </CardHeader>
      <CardContent className="min-h-0 flex-1 overflow-hidden py-0">
        <Table className="table-fixed" containerClassName="h-full overflow-auto">
          {/* Cells carry the background too: a background on thead alone does
              not paint over rows scrolling beneath a sticky header. */}
          <TableHeader className="sticky top-0 z-10 bg-card shadow-sm [&_th]:bg-card">
            <TableRow>
              <TableHead>Slot</TableHead>
              <TableHead>Player</TableHead>
              <TableHead className="text-center">Plan</TableHead>
              <TableHead className="text-center">Actual</TableHead>
              <TableHead className="text-center">Diff</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {budget.rows.map((row) => (
              <TableRow key={row.key}>
                <TableCell className="truncate text-xs font-medium">{row.label}</TableCell>
                <TableCell className="truncate text-xs font-medium">{row.filled_by && <>{row.filled_by}</>}</TableCell>
                <TableCell className="text-center tabular-nums">{row.planned !== null ? money(row.planned) : '—'}</TableCell>
                <TableCell className="text-center tabular-nums">{row.actual !== null ? money(row.actual) : '—'}</TableCell>
                <TableCell className="text-center tabular-nums">{difference(differenceFor(row))}</TableCell>
              </TableRow>
            ))}
            <TableRow>
              <TableCell>&nbsp;</TableCell>
              <TableCell>&nbsp;</TableCell>
              <TableCell className="text-center font-bold tabular-nums">{money(planned)}</TableCell>
              <TableCell className="text-center font-bold tabular-nums">{money(spent)}</TableCell>
              <TableCell className="text-center tabular-nums">&nbsp;</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}
