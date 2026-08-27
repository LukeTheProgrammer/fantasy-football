import { cn } from '@/common/helpers/cn';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { money } from '@/modules/drafts/helpers/money';
import { type LeagueMember, type RosterSlot, type RosterSlotPlayer } from '@/types/models';

interface TeamRostersProps {
  members: LeagueMember[];
  /** Roster slots keyed by league member id. */
  rosters: Record<number, RosterSlot[]>;
  isAuction: boolean;
}

/**
 * Every team's draft, read as the roster it built rather than as the order the
 * picks came in: what a team ended up with is the thing worth looking at once
 * the draft is over.
 */
export function TeamRosters({ members, rosters, isAuction }: TeamRostersProps) {
  return (
    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
      {members.map((member) => (
        <TeamRosterCard key={member.id} member={member} slots={rosters[member.id] ?? []} isAuction={isAuction} />
      ))}
    </div>
  );
}

function TeamRosterCard({ member, slots, isAuction }: { member: LeagueMember; slots: RosterSlot[]; isAuction: boolean }) {
  const filled = slots.filter((slot) => slot.player !== null);
  const spent = filled.reduce((total, slot) => total + (slot.player?.amount ?? 0), 0);

  return (
    <Card>
      <CardHeader>
        <CardTitle className="truncate text-base">{member.team_name}</CardTitle>
        <div className="flex w-full justify-between">
          <p className="text-xs text-muted-foreground">{member.owner_name}</p>
          {isAuction && <p className="text-xs text-muted-foreground">{money(spent)}</p>}
        </div>
      </CardHeader>
      <CardContent className="space-y-2">
        {slots.length === 0 && <p className="text-sm text-muted-foreground">No picks recorded.</p>}

        {slots
          .filter((s) => s.slot !== 'IR')
          .map((slot) => (
            <div key={slot.index} className={cn('flex items-center gap-3', !slot.is_starter && 'text-muted-foreground')}>
              <span className="w-12 shrink-0 truncate text-xs font-medium">{slot.label === 'RB/WR/TE' ? 'FLEX' : slot.label}</span>
              <span className="min-w-0 flex-1 truncate text-sm">
                {slot.player ? (
                  <>
                    {slot.player.full_name}
                    {slot.slot === 'BE' && <span className="ml-1 text-xs text-muted-foreground">{slot.player.position_id}</span>}
                    <span className="ml-1 text-xs text-muted-foreground">{slot.player.team_id}</span>
                  </>
                ) : (
                  <span className="text-muted-foreground">—</span>
                )}
              </span>
              <span className="shrink-0 text-xs tabular-nums">{slot.player && cost(slot.player, isAuction)}</span>
            </div>
          ))}
      </CardContent>
    </Card>
  );
}

/**
 * What the slot cost: dollars in an auction, the pick it took in a snake.
 */
function cost(player: RosterSlotPlayer, isAuction: boolean): string {
  return isAuction ? money(player.amount) : `R${player.round} #${player.pick_number}`;
}
