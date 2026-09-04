import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type RosterSlot, type TeamRoster } from '@/types/picks';

interface PickRostersProps {
  onClear?: () => void;
  roster: TeamRoster | null;
}

/**
 * One team's squad laid out as the league's lineup.
 *
 * Keepers and picks sit in one list because they play the same: the rankings
 * decide who starts, and how a player was come by is said quietly at the end
 * of his row rather than by splitting the squad in two.
 */
export function PickRosters({ onClear, roster }: PickRostersProps) {
  if (!roster) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Roster</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">Pick a team from the column to see what it holds.</p>
        </CardContent>
      </Card>
    );
  }

  const starters = roster.slots.filter((slot) => slot.is_starter);
  const bench = roster.slots.filter((slot) => !slot.is_starter);

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-start justify-between gap-2">
          <span className="min-w-0">
            <span className="block truncate">{roster.team_name}</span>
            <span className="block text-xs font-normal text-muted-foreground">{roster.owner_name}</span>
          </span>
          {onClear && (
            <Button size="sm" variant="ghost" onClick={onClear}>
              Clear
            </Button>
          )}
        </CardTitle>
      </CardHeader>

      <CardContent>
        <ul className="space-y-0.5">
          {starters.map((slot) => (
            <SlotRow key={slot.index} slot={slot} />
          ))}
        </ul>

        <p className="mt-4 mb-1 text-xs tracking-wide text-muted-foreground uppercase">Bench</p>
        <ul className="space-y-0.5">
          {bench.map((slot) => (
            <SlotRow key={slot.index} slot={slot} />
          ))}
        </ul>
      </CardContent>
    </Card>
  );
}

function SlotRow({ slot }: { slot: RosterSlot }) {
  const player = slot.player;
  const pos = player?.position ?? '';

  return (
    <li className={cn('flex items-center gap-2 rounded px-1 py-0.5 text-sm', !player && 'opacity-50')}>
      <span className="w-14 shrink-0 text-[10px] tracking-wide text-muted-foreground uppercase">{slot.label}</span>

      {player ? (
        <>
          <PositionBadge position={pos == 'RB/WR/TE' ? 'Flex' : pos} />
          <span className="truncate">{player.full_name}</span>
          <span className="text-xs text-muted-foreground">{player.team}</span>
          <span className="ml-auto shrink-0 text-xs text-muted-foreground tabular-nums">{player.source}</span>
        </>
      ) : (
        <span className="text-sm text-muted-foreground">Empty</span>
      )}
    </li>
  );
}
