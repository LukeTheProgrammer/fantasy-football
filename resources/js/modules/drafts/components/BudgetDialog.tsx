import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { BudgetPlan } from '@/modules/drafts/components/BudgetPlan';
import { type AuctionBudget } from '@/types/models';
import { useState } from 'react';

interface BudgetDialogProps {
  budget: AuctionBudget;
  draftId: number;
}

/**
 * The auction budget plan away from the draft room, so a plan can be written
 * before draft day rather than only during it.
 */
export function BudgetDialog({ budget, draftId }: BudgetDialogProps) {
  const [open, setOpen] = useState(false);

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline">Edit Budget</Button>
      </DialogTrigger>
      <DialogContent className="flex max-h-[85vh] flex-col sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>Budget Plan</DialogTitle>
          <DialogDescription>Plan what each starting slot is worth to you out of your ${budget.budget} auction budget.</DialogDescription>
        </DialogHeader>
        {/* The plan card carries its own header and Save button, so it drops
            in here without any chrome of its own. */}
        <BudgetPlan budget={budget} draftId={draftId} className="border-0 py-0 shadow-none" />
      </DialogContent>
    </Dialog>
  );
}
