import { Button } from '@/components/ui/button';
import { BudgetPlan } from '@/modules/drafts/components/auction/BudgetPlan';
import { DraftPicks } from '@/modules/drafts/components/auction/DraftPicks';
import { PositionScarcity } from '@/modules/drafts/components/auction/PositionScarcity';
import { TeamRoster } from '@/modules/drafts/components/auction/TeamRoster';
import { type AuctionBudget, type AuctionPlayer, type AuctionTeam, type MarketPosition, type RosterSlot } from '@/types/models';
import { useState } from 'react';

type Panel = 'picks' | 'budget' | 'positions';

interface DraftSidePanelProps {
  draftId: number;
  /** Players already picked, newest pick first. */
  picks: AuctionPlayer[];
  teams: AuctionTeam[];
  teamsById: Map<number, AuctionTeam>;
  positions: MarketPosition[];
  /** The board's position filter, which the position table both shows and sets. */
  position: string | null;
  onPositionChange: (position: string | null) => void;
  /** The signed in user's own plan, when they have a team in this league. */
  budget: AuctionBudget | null;
  /** A team being sized up, which takes the column over while it is selected. */
  selectedTeam: AuctionTeam | null;
  selectedTeamSlots: RosterSlot[];
  onClearTeam: () => void;
  onUndo: (pickId: number) => void;
}

/**
 * The column beside the board, running one panel at a time: your own plan, the
 * position table, or the list of picks.
 *
 * Selecting a team from the budgets takes the column over for as long as that
 * team is selected, since a roster is only ever read against a live bid. Any of
 * the buttons drops it again, so a button always shows what it says.
 */
export function DraftSidePanel({
  draftId,
  picks,
  teams,
  teamsById,
  positions,
  position,
  onPositionChange,
  budget,
  selectedTeam,
  selectedTeamSlots,
  onClearTeam,
  onUndo,
}: DraftSidePanelProps) {
  const [panel, setPanel] = useState<Panel>('picks');

  const show = (next: Panel) => {
    setPanel(next);
    onClearTeam();
  };

  const buttons: { key: Panel; label: string }[] = [
    { key: 'picks', label: 'Picks' },
    ...(budget ? [{ key: 'budget' as const, label: 'Budget' }] : []),
    { key: 'positions', label: 'Positions' },
  ];

  return (
    <div className="flex min-h-0 flex-col gap-2">
      <div className="flex gap-1">
        {buttons.map((button) => (
          <Button key={button.key} size="sm" variant={panel === button.key && !selectedTeam ? 'default' : 'outline'} onClick={() => show(button.key)}>
            {button.label}
          </Button>
        ))}
      </div>

      <div className="min-h-0 flex-1 overflow-auto">
        {selectedTeam ? (
          <TeamRoster team={selectedTeam} slots={selectedTeamSlots} onClose={onClearTeam} />
        ) : panel === 'budget' && budget ? (
          <BudgetPlan budget={budget} draftId={draftId} />
        ) : panel === 'positions' ? (
          <PositionScarcity positions={positions} active={position} onSelect={onPositionChange} />
        ) : (
          <DraftPicks players={picks} teams={teams} teamsById={teamsById} draftId={draftId} onUndo={onUndo} />
        )}
      </div>
    </div>
  );
}
