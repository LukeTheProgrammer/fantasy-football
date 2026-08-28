import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { money } from '@/modules/drafts/helpers/money';
import { type AuctionBudget } from '@/types/models';
import { useForm } from '@inertiajs/react';
import { type ReactNode, useMemo, useState } from 'react';

interface BudgetDialogProps {
  budget: AuctionBudget;
  draftId: number;
  trigger?: ReactNode;
}

/**
 * The editable auction budget plan, slot by slot, out of the league's budget.
 */
export function BudgetDialog({ budget, draftId, trigger }: BudgetDialogProps) {
  const [open, setOpen] = useState(false);

  const { data, setData, put, processing, isDirty, reset } = useForm<{ allocations: Record<string, string> }>({
    allocations: Object.fromEntries(budget.rows.map((row) => [row.key, row.planned !== null ? String(row.planned) : ''])),
  });

  // Totals follow what is typed rather than what is saved, so the plan adds up
  // while it is being written.
  const planned = useMemo(() => Object.values(data.allocations).reduce((total, amount) => total + (Number(amount) || 0), 0), [data.allocations]);

  const unplanned = budget.budget - planned;

  const handleSave = () => {
    put(route('drafts.budget.update', draftId), {
      preserveScroll: true,
      onSuccess: () => setOpen(false),
    });
  };

  // Reopening after a cancel should show the saved plan, not the abandoned edit.
  const handleOpenChange = (next: boolean) => {
    if (!next) {
      reset();
    }

    setOpen(next);
  };

  return (
    <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogTrigger asChild>{trigger ?? <Button variant="outline">Edit Budget</Button>}</DialogTrigger>
      <DialogContent className="flex max-h-[85vh] flex-col sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>Budget Plan</DialogTitle>
          <DialogDescription>Plan what each starting slot is worth to you out of your ${budget.budget} auction budget.</DialogDescription>
        </DialogHeader>
        <div className="w-full flex justify-around gap-4">
          <p className="m-0 tabular-nums">
            {unplanned >= 0 ? `${money(unplanned)} remaining` : `${money(Math.abs(unplanned))} over`}
          </p>
          <p className="m-0 tabular-nums text-right text-lg font-bold">{money(planned)}</p>
        </div>
        <div className="min-h-0 flex-1 overflow-y-auto">
          <Table className="table-fixed" containerClassName="h-full overflow-auto">
            <TableHeader className="sticky top-0 z-10 bg-background shadow-sm [&_th]:bg-background">
              <TableRow>
                <TableHead className="w-[40%]">Slot</TableHead>
                <TableHead className="w-[30%] text-center">Plan</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {budget.rows.map((row) => (
                <TableRow key={row.key}>
                  <TableCell className="truncate text-xs font-medium">{row.label}</TableCell>
                  <TableCell className="px-1">
                    {/* <Input
                      type="number"
                      min={0}
                      inputMode="numeric"
                      placeholder="$"
                      className="h-8 text-center tabular-nums"
                      value={data.allocations[row.key] ?? ''}
                      onChange={(event) => setData('allocations', { ...data.allocations, [row.key]: event.target.value })}
                    /> */}
                    <InputGroup>
                      <InputGroupAddon>
                        <InputGroupText>$</InputGroupText>
                      </InputGroupAddon>
                      <InputGroupInput
                        type="numeric"
                        min={0}
                        placeholder="0"
                        className="h-8 text-center tabular-nums"
                        value={data.allocations[row.key] ?? ''}
                        onChange={(event) => setData('allocations', { ...data.allocations, [row.key]: event.target.value })}
                      />
                    </InputGroup>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => handleOpenChange(false)}>
            Cancel
          </Button>
          <Button onClick={handleSave} disabled={processing || !isDirty}>
            {processing ? 'Saving...' : 'Save'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
