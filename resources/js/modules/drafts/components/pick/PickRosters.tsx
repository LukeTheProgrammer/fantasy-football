import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type TeamRoster } from '@/types/picks';

interface PickRostersProps {
  onClear?: () => void;
  roster: TeamRoster | null;
}

/**
 * One team's squad, chosen from the column beside the board.
 *
 * Keepers and picks are kept apart because a keeper cost no pick, and reading
 * them as one list hides which of the two a player was.
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
        <p className="mb-1 text-xs tracking-wide text-muted-foreground uppercase">Keepers &middot; {roster.keepers.length}</p>
        <ul className="space-y-1">
          {roster.keepers.map((player) => (
            <li key={player.player_id} className="flex items-center gap-2 text-sm">
              <PositionBadge position={player.position ?? ''} />
              <span className="truncate">{player.full_name}</span>
              <span className="text-muted-foreground">{player.team}</span>
            </li>
          ))}
        </ul>

        <p className="mt-4 mb-1 text-xs tracking-wide text-muted-foreground uppercase">Drafted &middot; {roster.picks.length}</p>
        {roster.picks.length === 0 && <p className="text-sm text-muted-foreground">No picks yet.</p>}
        <ul className="space-y-1">
          {roster.picks.map((pick) => (
            <li key={pick.pick_id} className="flex items-center gap-2 text-sm">
              <span className="w-8 text-xs text-muted-foreground tabular-nums">R{pick.round}</span>
              <PositionBadge position={pick.position ?? ''} />
              <span className="truncate">{pick.full_name}</span>
              <span className="text-muted-foreground">{pick.team}</span>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  );
}
