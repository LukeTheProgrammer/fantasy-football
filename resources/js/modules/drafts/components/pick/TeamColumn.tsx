import { cn } from '@/common/helpers/cn';
import { type TeamRoster } from '@/types/picks';

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
              'flex flex-col justify-center rounded-lg border bg-card px-2 py-1.5 text-left transition-colors hover:border-primary/60',
              selectedTeamId === roster.league_member_id && 'border-primary ring-1 ring-primary',
              onTheClock && 'border-primary bg-primary/10',
              remaining === 0 && !onTheClock && 'opacity-60',
            )}
          >
            <div className="flex items-baseline justify-between gap-1">
              <p className="truncate text-[11px] leading-tight font-semibold">{roster.team_name}</p>
              {onTheClock && <span className="text-[9px] tracking-wide text-primary uppercase">On now</span>}
            </div>
            <div className="mt-0.5 flex items-baseline justify-between">
              <span className="text-base leading-none font-bold tabular-nums">{roster.picks.length}</span>
              <span className="text-right text-[10px] leading-tight text-muted-foreground tabular-nums">{remaining} left</span>
            </div>
          </button>
        );
      })}
    </div>
  );
}
