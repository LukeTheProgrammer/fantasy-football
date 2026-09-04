import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type DraftRanking } from '@/types/models';
import { useMemo, useState } from 'react';

const POSITIONS = ['ALL', 'QB', 'RB', 'WR', 'TE', 'DST', 'K'];

interface PickBoardProps {
  players: DraftRanking[];
  canRecord: boolean;
  recording: boolean;
  onDraft: (playerId: number) => void;
}

/**
 * Everyone still available, best first. Drafting is one click because the
 * order already knows whose pick it is.
 */
export function PickBoard({ players, canRecord, recording, onDraft }: PickBoardProps) {
  const [filterText, setFilterText] = useState('');
  const [position, setPosition] = useState('ALL');

  const filtered = useMemo(() => {
    const search = filterText.trim().toLowerCase();

    return players.filter(({ player }) => {
      if (position !== 'ALL' && player.position_id?.toUpperCase() !== position) {
        return false;
      }

      if (!search) {
        return true;
      }

      return (
        player.full_name.toLowerCase().includes(search) ||
        (player.position_id?.toLowerCase().includes(search) ?? false) ||
        (player.team_id?.toLowerCase().includes(search) ?? false)
      );
    });
  }, [players, filterText, position]);

  return (
    <Card>
      <CardHeader>
        <CardTitle className="grid w-full grid-cols-3 gap-3">
          <div className="col-span-2 flex flex-wrap items-center gap-2">
            <div className="flex gap-1">
              {POSITIONS.map((pos) => (
                <Button key={pos} size="sm" variant={position === pos ? 'default' : 'outline'} onClick={() => setPosition(pos)}>
                  {pos}
                </Button>
              ))}
            </div>
          </div>
          <Input className="w-full" placeholder="Filter players..." value={filterText} onChange={(e) => setFilterText(e.target.value)} />
        </CardTitle>
      </CardHeader>

      <CardContent>
        <div className="overflow-y-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Rank</TableHead>
                <TableHead>Player</TableHead>
                <TableHead>Pos</TableHead>
                <TableHead>Team</TableHead>
                <TableHead>Tier</TableHead>
                <TableHead>ADP</TableHead>
                <TableHead className="text-right">&nbsp;</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filtered.map((ranking) => (
                <TableRow key={ranking.id}>
                  <TableCell className="text-muted-foreground">{ranking.rank}</TableCell>
                  <TableCell className="font-medium">{ranking.player.full_name}</TableCell>
                  <TableCell>
                    <PositionBadge position={ranking.player.position ?? ranking.player.position_id} />
                  </TableCell>
                  <TableCell>{ranking.player.team?.abbreviation ?? ranking.player.team_id}</TableCell>
                  <TableCell>{ranking.tier}</TableCell>
                  <TableCell>{ranking.adp}</TableCell>
                  <TableCell className="text-right">
                    <Button size="sm" disabled={!canRecord || recording} onClick={() => onDraft(ranking.player_id)}>
                      Draft
                    </Button>
                  </TableCell>
                </TableRow>
              ))}

              {filtered.length === 0 && (
                <TableRow>
                  <TableCell colSpan={7} className="py-8 text-center text-muted-foreground">
                    No players match that filter.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>
  );
}
