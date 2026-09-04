import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { EditPickDialog } from '@/modules/drafts/components/auction/EditPickDialog';
import { money } from '@/modules/drafts/helpers/money';
import { type AuctionPlayer, type AuctionTeam } from '@/types/models';
import { Edit, Search, Undo2 } from 'lucide-react';
import { useMemo, useState } from 'react';

interface DraftPicksProps {
  /** Players already picked, newest pick first. */
  players: AuctionPlayer[];
  teams: AuctionTeam[];
  teamsById: Map<number, AuctionTeam>;
  draftId: number;
  onUndo: (pickId: number) => void;
}

/**
 * What has gone so far, newest first. Every pick can be undone, since a price
 * mistyped mid auction is worse than no record at all.
 */
export function DraftPicks({ players, teams, teamsById, draftId, onUndo }: DraftPicksProps) {
  const [editingId, setEditingId] = useState<number | null>(null);
  const [search, setSearch] = useState('');

  // Read from the current props rather than holding a copy, so an edit that
  // lands is reflected the moment the page data comes back.
  const editing = players.find((player) => player.player_id === editingId) ?? null;

  const found = useMemo(() => {
    const term = search.trim().toLowerCase();

    if (!term) {
      return players;
    }

    return players.filter((player) => player.full_name?.toLowerCase().includes(term));
  }, [players, search]);

  return (
    <Card className="flex h-full min-h-0 flex-col overflow-hidden">
      <CardHeader className="py-0">
        <CardTitle>
          <div className="flex flex-wrap items-center justify-between gap-2">
            <span className="text-base">Picks</span>
            <div className="relative max-w-xs flex-1">
              <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
              <Input placeholder="Search picks..." value={search} onChange={(event) => setSearch(event.target.value)} className="pl-8" />
            </div>
          </div>
        </CardTitle>
      </CardHeader>
      <CardContent className="min-h-0 flex-1 overflow-hidden py-0">
        {players.length === 0 || found.length === 0 ? (
          <p className="py-6 text-center text-sm text-muted-foreground">
            {players.length === 0 ? 'Nothing picked yet.' : 'No picks match that search.'}
          </p>
        ) : (
          <Table className="table-fixed" containerClassName="h-full overflow-auto">
            {/* Cells carry the background too: a background on thead alone does
                not paint over rows scrolling beneath a sticky header. */}
            <TableHeader className="sticky top-0 z-10 bg-card shadow-sm [&_th]:bg-card">
              <TableRow>
                <TableHead className="w-[31%]">Player</TableHead>
                <TableHead className="w-[26%]">Team</TableHead>
                <TableHead className="w-[18%] text-center" title="Price paid, coloured against what the board marked him at">
                  Price
                </TableHead>
                <TableHead className="w-[25%]" />
              </TableRow>
            </TableHeader>
            <TableBody>
              {found.map((player) => {
                // Read against the board's own price: red went over, green came
                // in under. It is the fastest way to see who is spending badly.
                const value = player.market_value ?? player.projected_value;
                const difference = value !== null && player.drafted_for !== null ? player.drafted_for - value : null;

                return (
                  <TableRow key={player.player_id}>
                    <TableCell className="truncate font-medium">{player.full_name}</TableCell>
                    <TableCell className="max-w-[9rem] truncate text-xs text-muted-foreground">
                      {teamsById.get(player.drafted_by ?? 0)?.team_name}
                    </TableCell>
                    <TableCell
                      className={cn(
                        'text-center font-medium tabular-nums',
                        difference !== null && difference > 0 && 'text-destructive',
                        difference !== null && difference < 0 && 'text-emerald-600 dark:text-emerald-500',
                      )}
                      title={difference === null ? undefined : `${difference > 0 ? '+' : ''}${difference} against $${value}`}
                    >
                      {money(player.drafted_for)}
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center justify-end gap-1">
                        <Button size="sm" variant="secondary" onClick={() => player.pick_id && onUndo(player.pick_id)}>
                          <Undo2 className="h-4 w-4" />
                        </Button>
                        <Button size="sm" variant="secondary" onClick={() => setEditingId(player.player_id)}>
                          <Edit className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        )}
      </CardContent>

      <EditPickDialog key={editing?.pick_id ?? 'none'} player={editing} teams={teams} draftId={draftId} onClose={() => setEditingId(null)} />
    </Card>
  );
}
