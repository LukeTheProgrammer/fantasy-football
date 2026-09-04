import { cn } from '@/common/helpers/cn';
import { type AuctionTeam } from '@/types/models';

interface TeamBudgetsProps {
  teams: AuctionTeam[];
  selectedTeamId?: number | null;
  onSelect?: (teamId: number) => void;
}

/**
 * What every team can still do. Max bid is the number that matters mid auction:
 * what is left after reserving a dollar for each of their other open spots.
 *
 * Sits beside the board rather than above it, so the whole league fits on
 * screen without pushing players out of view. That is why the cards are dense:
 * the dollar figures stay large, everything else gives up its space.
 *
 * The cards share the column's height evenly. Once the league outgrows the
 * space, the rows stop stretching and the column scrolls instead.
 */
export function TeamBudgets({ teams, selectedTeamId, onSelect }: TeamBudgetsProps) {
  return (
    <div className="grid h-full auto-rows-fr gap-1">
      {teams.map((team) => (
        <button
          key={team.id}
          type="button"
          onClick={() => onSelect?.(team.id)}
          className={cn(
            'flex flex-col justify-center rounded-lg border bg-card px-2 py-1.5 text-left transition-colors hover:border-primary/60',
            selectedTeamId === team.id && 'border-primary ring-1 ring-primary',
            team.open_spots === 0 && 'opacity-60',
          )}
        >
          <div className="flex items-baseline justify-between">
            <p className="truncate text-[11px] leading-tight font-semibold">{team.team_name}</p>
            <p className="text-right text-[10px] leading-tight text-muted-foreground tabular-nums">${team.max_bid}</p>
          </div>
          <div className="mt-0.5 flex items-baseline justify-between">
            <div>
              <span className="text-base leading-none font-bold tabular-nums">
                {team.filled} / {team.filled + team.open_spots}
              </span>
            </div>
            <div className="text-right">
              <p className="text-base leading-none font-bold tabular-nums">${team.remaining}</p>
            </div>
          </div>
        </button>
      ))}
    </div>
  );
}
