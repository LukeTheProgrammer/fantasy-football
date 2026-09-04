import { cn } from '@/common/helpers/cn';
import { type TeamRoster } from '@/types/picks';
import { Clock } from 'lucide-react';

interface TeamColumnProps {
  onSelect?: (leagueMemberId: number) => void;
  onTheClockMemberId: number | null;
  /** Picks each team still holds, by league member id. */
  remainingByMember: Record<number, number>;
  rosters: TeamRoster[];
  selectedTeamId?: number | null;
}

/**
 * The whole league down the left of the board.
 *
 * A pick draft has no budget to watch, so what a team is worth knowing by is
 * how many picks it has left — which is not the same for everyone here,
 * because picks in this league get traded.
 *
 * The cards share the column's height evenly and the column scrolls once the
 * league outgrows it, the same as the auction room's budgets.
 * <span className="text-[9px] tracking-wide text-primary uppercase">ON THE CLOCK</span>
 */
export function TeamColumn({ onSelect, onTheClockMemberId, remainingByMember, rosters, selectedTeamId }: TeamColumnProps) {
  return (
    <div className="grid h-full auto-rows-fr gap-1">
      {rosters.map((roster) => {
        const remaining = remainingByMember[roster.league_member_id] ?? 0;
        const onTheClock = roster.league_member_id === onTheClockMemberId;

        return (
          <button
            key={roster.league_member_id}
            type="button"
            onClick={() => onSelect?.(roster.league_member_id)}
            className={cn(
              'min-h-10 rounded-lg border bg-card px-2 py-1.5 text-left transition-colors hover:border-primary/60',
              selectedTeamId === roster.league_member_id && 'border-primary ring-1 ring-primary',
              remaining === 0 && !onTheClock && 'opacity-60',
            )}
          >
            <div className="h-full w-full flex flex-col items-baseline justify-between gap-1">
              <div className="flex w-full items-start justify-between gap-1">
                <p className="text-md truncate leading-tight font-bold">{roster.team_name}</p>
                <div>{onTheClock && <Clock size="12" />}</div>
              </div>
              <div className="flex w-full items-end justify-between gap-1">
                <p className="text-xs leading-tight text-muted-foreground">{roster.owner_name}</p>
                <RosterCount roster={roster} />
              </div>
            </div>
          </button>
        );
      })}
    </div>
  );
}

function RosterCount({ roster }: { roster: TeamRoster }) {
  let rostered = 0;
  let total = 0;

  for (const slot of roster.slots) {
    total++;
    if (slot.player !== null) {
      rostered++;
    }
  }

  return (
    <p className="text-xs leading-tight">
      {rostered} / {total}
    </p>
  );
}
