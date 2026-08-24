import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { money } from '@/modules/drafts/helpers/money';
import { type AuctionPlayer, type AuctionTeam } from '@/types/models';
import { Undo2 } from 'lucide-react';

interface SoldPlayersProps {
  /** Players already sold, newest sale first. */
  players: AuctionPlayer[];
  teamsById: Map<number, AuctionTeam>;
  onUndo: (pickId: number) => void;
}

/**
 * What has gone so far, newest first. Every sale can be undone, since a price
 * mistyped mid auction is worse than no record at all.
 */
export function SoldPlayers({ players, teamsById, onUndo }: SoldPlayersProps) {
  return (
    <Card className="max-h-[28rem] overflow-auto">
      <CardHeader className="py-0">
        <CardTitle className="text-base">Sold</CardTitle>
      </CardHeader>
      <CardContent className="py-0">
        {players.length === 0 ? (
          <p className="py-6 text-center text-sm text-muted-foreground">Nothing sold yet.</p>
        ) : (
          <ul className="divide-y">
            {players.map((player) => (
              <li key={player.player_id} className="flex items-center justify-between gap-2 py-2">
                <div className="min-w-0">
                  <p className="truncate text-sm font-medium">{player.full_name}</p>
                  <p className="truncate text-xs text-muted-foreground">
                    {teamsById.get(player.drafted_by ?? 0)?.team_name} · {money(player.drafted_for)}
                  </p>
                </div>
                <Button size="sm" variant="ghost" onClick={() => player.pick_id && onUndo(player.pick_id)}>
                  <Undo2 className="h-4 w-4" />
                </Button>
              </li>
            ))}
          </ul>
        )}
      </CardContent>
    </Card>
  );
}
