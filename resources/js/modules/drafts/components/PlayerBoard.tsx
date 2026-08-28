import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { money } from '@/modules/drafts/helpers/money';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type AuctionPlayer, type AuctionTeam } from '@/types/models';
import { Search } from 'lucide-react';
import { useEffect, useRef, type RefObject } from 'react';

const POSITIONS = ['QB', 'RB', 'WR', 'TE', 'K', 'DST'];

interface PlayerBoardProps {
  /** Players already filtered for display. */
  players: AuctionPlayer[];
  teamsById: Map<number, AuctionTeam>;
  /** Season the rankings are for, used to label the previous year's price. */
  season: number;
  nominatedId: number | null;
  onNominate: (playerId: number) => void;
  search: string;
  onSearchChange: (search: string) => void;
  position: string | null;
  onPositionChange: (position: string | null) => void;
  showPicked: boolean;
  onShowPickedChange: (showPicked: boolean) => void;
  /** Row the keyboard is on, as an index into `players`. */
  activeIndex: number;
  onActiveIndexChange: (index: number) => void;
  /** Held by the page so `/` can put the cursor in the search box. */
  searchRef?: RefObject<HTMLInputElement | null>;
}

/**
 * The draft board: every rankable player with both value estimates, filtered
 * down to what is worth looking at right now. Clicking a row puts that player
 * up for bidding.
 */
export function PlayerBoard({
  players,
  teamsById,
  season,
  nominatedId,
  onNominate,
  search,
  onSearchChange,
  position,
  onPositionChange,
  showPicked,
  onShowPickedChange,
  activeIndex,
  onActiveIndexChange,
  searchRef,
}: PlayerBoardProps) {
  const activeRow = useRef<HTMLTableRowElement | null>(null);

  // Arrowing off the visible rows has to bring them with it, or the keyboard
  // walks the board somewhere the eye cannot follow.
  useEffect(() => {
    activeRow.current?.scrollIntoView({ block: 'nearest' });
  }, [activeIndex]);

  return (
    <Card className="flex h-full min-h-0 flex-col">
      <CardHeader className="py-0">
        <CardTitle>
          <div className="flex flex-wrap items-center justify-between gap-2">
            <div className="flex flex-wrap items-center gap-1">
              <Button size="sm" variant={position === null ? 'default' : 'outline'} onClick={() => onPositionChange(null)}>
                All
              </Button>
              {POSITIONS.map((pos) => (
                <Button
                  key={pos}
                  size="sm"
                  variant={position === pos ? 'default' : 'outline'}
                  onClick={() => onPositionChange(position === pos ? null : pos)}
                >
                  {pos}
                </Button>
              ))}
              <Button size="sm" variant={showPicked ? 'default' : 'outline'} onClick={() => onShowPickedChange(!showPicked)}>
                Show picked
              </Button>
            </div>
            <div className="relative max-w-xs flex-1">
              <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
              <Input
                ref={searchRef}
                placeholder="Search players... ( / )"
                value={search}
                onChange={(event) => onSearchChange(event.target.value)}
                className="pl-8"
              />
            </div>
          </div>
        </CardTitle>
      </CardHeader>
      <CardContent className="flex-grow overflow-hidden py-0">
        <Table containerClassName="h-full overflow-auto">
          {/* Cells carry the background too: a background on thead alone does not
              paint over rows scrolling beneath a sticky header. */}
          <TableHeader className="sticky top-0 z-10 bg-card shadow-sm [&_th]:bg-card">
            <TableRow>
              <TableHead className="w-12 text-center">Rank</TableHead>
              <TableHead>Player</TableHead>
              <TableHead className="w-12 text-center">Tier</TableHead>
              <TableHead className="w-20 text-center">League</TableHead>
              <TableHead className="w-20 text-center">VAR</TableHead>
              <TableHead className="w-20 text-center">ADV</TableHead>
              <TableHead className="w-20 text-center">{season - 1}</TableHead>
              <TableHead className="w-12 text-center">Bye</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {players.map((player, index) => (
              <TableRow
                key={player.player_id}
                ref={index === activeIndex ? activeRow : undefined}
                onClick={() => {
                  onActiveIndexChange(index);
                  onNominate(player.player_id);
                }}
                className={cn(
                  'cursor-pointer',
                  nominatedId === player.player_id && 'bg-muted',
                  index === activeIndex && 'ring-1 ring-primary ring-inset',
                  player.drafted_by !== null && 'opacity-50',
                )}
              >
                <TableCell className="text-center tabular-nums">{player.rank}</TableCell>
                <TableCell>
                  <div className="flex items-center gap-2">
                    <PositionBadge position={player.position_id} />
                    <span className="font-medium">{player.full_name}</span>
                    <span className="text-xs text-muted-foreground">{player.team_id}</span>
                    {player.drafted_by !== null && (
                      <span className="text-xs text-muted-foreground">
                        — {teamsById.get(player.drafted_by)?.team_name} {money(player.drafted_for)}
                      </span>
                    )}
                  </div>
                </TableCell>
                <TableCell className="text-center tabular-nums">{player.tier ?? '—'}</TableCell>
                <TableCell className="text-center font-medium tabular-nums">{money(player.market_value)}</TableCell>
                <TableCell className="text-center font-medium tabular-nums">{money(player.projected_value)}</TableCell>
                <TableCell className="text-center text-muted-foreground tabular-nums">{money(player.adv)}</TableCell>
                <TableCell className="text-center text-muted-foreground tabular-nums">{money(player.previous_price)}</TableCell>
                <TableCell className="text-center text-xs text-muted-foreground tabular-nums">{player.bye_week ?? '—'}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}
