import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { money } from '@/modules/drafts/helpers/money';
import type { RosterSlotPlayer } from '@/types/models';

interface PositionPlayersProps {
  position: string;
  players: RosterSlotPlayer[];
  isAuction: boolean;
  /** Everything spent at the position, which is more than the listed players cost. */
  spent: number;
}

/**
 * The players who went for the most at one position, best first.
 */

export function PositionPlayers({ position, players, isAuction, spent }: PositionPlayersProps) {
  return (
    <Card>
      <CardHeader>
        <div className="flex items-start justify-between gap-2">
          <div>
            <CardTitle>{position}</CardTitle>
            <CardDescription className="sr-only">
              Top {players.length} {position}s drafted.
            </CardDescription>
          </div>
          {isAuction && (
            <div className="text-right">
              <p className="text-lg font-semibold tabular-nums">{money(spent)}</p>
            </div>
          )}
        </div>
      </CardHeader>
      <CardContent>
        <div className="flex flex-col gap-2">
          {players.length === 0 && <p className="text-sm text-muted-foreground">Nobody was drafted at this position.</p>}

          {players.map((player, index) => (
            <div key={player.pick_id} className="flex items-center justify-between gap-2">
              <span className="min-w-0 truncate text-sm">
                <span className="mr-1 text-xs text-muted-foreground tabular-nums">{index + 1}.</span>
                {player.full_name}
                {player.team_id && <span className="ml-1 text-xs text-muted-foreground">{player.team_id}</span>}
              </span>
              <span className="shrink-0 text-sm font-medium tabular-nums">{cost(player, isAuction)}</span>
            </div>
          ))}
        </div>
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
