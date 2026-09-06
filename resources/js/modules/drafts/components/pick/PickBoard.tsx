import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PlayerDialog } from '@/modules/drafts/components/pick/PlayerDialog';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type BoardPlayer } from '@/types/picks';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { useMemo, useState } from 'react';

const POSITIONS = ['ALL', 'QB', 'RB', 'WR', 'TE', 'FLEX', 'DST', 'K'];

/** Filters that stand for more than one position, so the board can be read the way a lineup slot is. */
const POSITION_GROUPS: Record<string, string[]> = {
  FLEX: ['RB', 'WR', 'TE'],
};

type SortKey = 'rank' | 'full_name' | 'position' | 'team' | 'tier' | 'adp' | 'delta' | 'points' | 'par' | 'dynasty';

type Direction = 'asc' | 'desc';

interface Column {
  /** Which way round is "best first", so one click sorts the useful way. */
  best: Direction;
  key: SortKey;
  label: string;
  title?: string;
}

const COLUMNS: Column[] = [
  { key: 'rank', label: 'Rank', best: 'asc' },
  { key: 'full_name', label: 'Player', best: 'asc' },
  { key: 'position', label: 'Pos', best: 'asc' },
  { key: 'team', label: 'Team', best: 'asc' },
  { key: 'tier', label: 'Tier', best: 'asc' },
  { key: 'adp', label: 'ADP', best: 'asc', title: "ESPN's average draft position" },
  { key: 'delta', label: '+/-', best: 'desc', title: 'Picks past his ADP: positive means he has fallen' },
  { key: 'points', label: 'Proj', best: 'desc', title: 'Projected points per week' },
  { key: 'par', label: 'PAR', best: 'desc', title: 'Projected points per week above the last starter at his position' },
  { key: 'dynasty', label: 'Dyn', best: 'asc', title: "FantasyPros' dynasty rank, published in full PPR: an ordering, not a score" },
];

interface PickBoardProps {
  draftId: number;
  players: BoardPlayer[];
  canRecord: boolean;
  /** The overall pick the room is on, which is what ADP is read against. */
  currentPick: number | null;
  recording: boolean;
  onDraft: (playerId: number) => void;
}

/**
 * Everyone still available, best first. Drafting is one click because the
 * order already knows whose pick it is.
 *
 * Every column sorts, because the estimates on the board disagree and the
 * disagreement is the point: the room wants to read the same pool by expert
 * rank, by what a player is projected to score and by what a keeper is worth,
 * without any one of those being made the permanent truth.
 */
export function PickBoard({ draftId, players, canRecord, currentPick, recording, onDraft }: PickBoardProps) {
  const [filterText, setFilterText] = useState('');
  const [position, setPosition] = useState('ALL');
  // Null is the order the board arrived in, which is already by rank.
  const [sort, setSort] = useState<{ direction: Direction; key: SortKey } | null>(null);

  const filtered = useMemo(() => {
    const search = filterText.trim().toLowerCase();

    return players.filter((player) => {
      if (position !== 'ALL') {
        const group = POSITION_GROUPS[position];
        const playerPosition = player.position?.toUpperCase();

        if (group ? !group.includes(playerPosition ?? '') : playerPosition !== position) {
          return false;
        }
      }

      if (!search) {
        return true;
      }

      return (
        (player.full_name?.toLowerCase().includes(search) ?? false) ||
        (player.position?.toLowerCase().includes(search) ?? false) ||
        (player.team?.toLowerCase().includes(search) ?? false)
      );
    });
  }, [players, filterText, position]);

  const sorted = useMemo(() => {
    if (sort === null) {
      return filtered;
    }

    const factor = sort.direction === 'asc' ? 1 : -1;

    return [...filtered].sort((a, b) => {
      const left = sortValue(a, sort.key, currentPick);
      const right = sortValue(b, sort.key, currentPick);

      // A player the source has no opinion about sits at the bottom either
      // way round: he is unranked rather than ranked last.
      if (left === null || right === null) {
        return left === right ? 0 : left === null ? 1 : -1;
      }

      if (typeof left === 'string' || typeof right === 'string') {
        return String(left).localeCompare(String(right)) * factor;
      }

      return (left - right) * factor;
    });
  }, [filtered, sort, currentPick]);

  const toggleSort = (column: Column) => {
    setSort((current) =>
      current?.key === column.key
        ? { key: column.key, direction: current.direction === 'asc' ? 'desc' : 'asc' }
        : { key: column.key, direction: column.best },
    );
  };

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
                {COLUMNS.map((column) => (
                  <SortableHead key={column.key} column={column} sort={sort} onSort={toggleSort} />
                ))}
                <TableHead className="text-right">&nbsp;</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {sorted.map((ranking) => (
                <TableRow key={ranking.id}>
                  <TableCell className="text-muted-foreground">{ranking.rank}</TableCell>
                  <TableCell className="font-medium">
                    <PlayerDialog draftId={draftId} name={ranking.full_name} playerId={ranking.player_id} />
                  </TableCell>
                  <TableCell>
                    <PositionBadge position={ranking.position ?? ''} />
                  </TableCell>
                  <TableCell>{ranking.team}</TableCell>
                  <TableCell>{ranking.tier}</TableCell>
                  <TableCell className="tabular-nums">{ranking.adp ?? '—'}</TableCell>
                  <TableCell>
                    <AdpDelta adp={ranking.adp} currentPick={currentPick} />
                  </TableCell>
                  <TableCell className="tabular-nums">{ranking.points ?? '—'}</TableCell>
                  <TableCell className="font-medium tabular-nums">{ranking.par ?? '—'}</TableCell>
                  <TableCell className="tabular-nums">{ranking.dynasty ?? '—'}</TableCell>
                  <TableCell className="text-right">
                    <Button size="sm" disabled={!canRecord || recording} onClick={() => onDraft(ranking.player_id)}>
                      Draft
                    </Button>
                  </TableCell>
                </TableRow>
              ))}

              {sorted.length === 0 && (
                <TableRow>
                  <TableCell colSpan={11} className="py-8 text-center text-muted-foreground">
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

/**
 * One column heading, which sorts the board when it is clicked.
 */
function SortableHead({
  column,
  sort,
  onSort,
}: {
  column: Column;
  sort: { direction: Direction; key: SortKey } | null;
  onSort: (column: Column) => void;
}) {
  const active = sort?.key === column.key;

  return (
    <TableHead title={column.title}>
      <button type="button" className="flex items-center gap-1 hover:text-foreground" onClick={() => onSort(column)}>
        <span className={active ? 'font-semibold text-foreground' : undefined}>{column.label}</span>
        {active && (sort.direction === 'asc' ? <ChevronUp className="size-3" /> : <ChevronDown className="size-3" />)}
      </button>
    </TableHead>
  );
}

/**
 * The value one column sorts on, with anything the source left empty reported
 * as null so it can be pushed to the bottom.
 */
function sortValue(player: BoardPlayer, key: SortKey, currentPick: number | null): number | string | null {
  if (key === 'delta') {
    return currentPick === null || player.adp === null || player.adp <= 0 ? null : currentPick - player.adp;
  }

  const value = player[key];

  return value === null || value === '' ? null : value;
}

/**
 * How far past his ADP a player has fallen, as of the pick on the clock.
 *
 * Positive is the interesting direction: the room has let him go longer than
 * the field usually does, so he is going cheaper than he is drafted for.
 */
function AdpDelta({ adp, currentPick }: { adp: number | null; currentPick: number | null }) {
  if (currentPick === null || adp === null || adp <= 0) {
    return <span className="text-muted-foreground">—</span>;
  }

  const delta = Math.round(currentPick - adp);

  if (delta === 0) {
    return <span className="text-muted-foreground tabular-nums">0</span>;
  }

  return (
    <span className={delta > 0 ? 'font-medium text-emerald-600 tabular-nums' : 'text-muted-foreground tabular-nums'}>
      {delta > 0 ? `+${delta}` : delta}
    </span>
  );
}
