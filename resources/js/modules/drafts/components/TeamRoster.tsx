import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { money } from '@/modules/drafts/helpers/money';
import { type AuctionTeam, type RosterSlot } from '@/types/models';
import { X } from 'lucide-react';

interface TeamRosterProps {
  team: AuctionTeam;
  slots: RosterSlot[];
  onClose: () => void;
}

/**
 * One team's picks in their roster spots, so a glance says what they still
 * need and what they have left to spend on it.
 */
export function TeamRoster({ team, slots, onClose }: TeamRosterProps) {
  const starters = slots.filter((slot) => slot.is_starter);
  const startersFilled = starters.filter((slot) => slot.player !== null).length;

  return (
    <Card className="flex h-full min-h-0 flex-col overflow-hidden">
      <CardHeader className="py-0">
        <CardTitle>
          <div className="flex items-center justify-between gap-2">
            <div className="min-w-0">
              <p className="truncate text-base">{team.team_name}</p>
              <p className="text-xs font-normal text-muted-foreground tabular-nums">
                {money(team.remaining)} left · max {money(team.max_bid)} · {startersFilled}/{starters.length} starters
              </p>
            </div>
            <Button size="sm" variant="ghost" onClick={onClose} aria-label="Close roster">
              <X className="h-4 w-4" />
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
              <TableHead className="w-[22%]">Slot</TableHead>
              <TableHead className="w-[56%]">Player</TableHead>
              <TableHead className="w-[22%] text-center">Price</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {slots.map((slot) => (
              <TableRow key={slot.index} className={cn(!slot.is_starter && 'text-muted-foreground')}>
                <TableCell className="truncate text-xs font-medium">{slot.label}</TableCell>
                <TableCell className="truncate">
                  {slot.player ? (
                    <span>
                      {slot.player.full_name}
                      <span className="ml-1 text-xs text-muted-foreground">{slot.player.position_id}</span>
                    </span>
                  ) : (
                    <span className="text-muted-foreground">—</span>
                  )}
                </TableCell>
                <TableCell className="text-center tabular-nums">{slot.player ? money(slot.player.amount) : ''}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}
