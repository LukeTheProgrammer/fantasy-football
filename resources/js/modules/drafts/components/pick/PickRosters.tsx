import { cn } from '@/common/helpers/cn';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PlayerDialog } from '@/modules/drafts/components/pick/PlayerDialog';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type RosterSlot, type TeamRoster } from '@/types/picks';

interface PickRostersProps {
  draftId: number;
  onSelectTeam: (leagueMemberId: number) => void;
  roster: TeamRoster | null;
  /** Every team in the league, so any of them can be reached from the heading. */
  rosters: TeamRoster[];
}

/**
 * One team's squad laid out as the league's lineup.
 *
 * Keepers and picks sit in one list because they play the same: the rankings
 * decide who starts, and how a player was come by is said quietly at the end
 * of his row rather than by splitting the squad in two.
 *
 * The heading names the team and also chooses it. Clicking a slot in the round
 * is the quick way to a roster and reaches only the twelve teams picking in
 * that round; the select reaches all of them, and is the way to a team whose
 * round has already gone by.
 */
export function PickRosters({ draftId, onSelectTeam, roster, rosters }: PickRostersProps) {
  const starters = roster?.slots.filter((slot) => slot.is_starter) ?? [];
  const bench = roster?.slots.filter((slot) => !slot.is_starter) ?? [];

  return (
    <Card>
      <CardHeader>
        <CardTitle className="space-y-1">
          <Select value={roster ? String(roster.league_member_id) : undefined} onValueChange={(value) => onSelectTeam(Number(value))}>
            <SelectTrigger className="w-full" aria-label="Team roster">
              <SelectValue placeholder="Choose a team" />
            </SelectTrigger>
            <SelectContent>
              {rosters.map((team) => (
                <SelectItem key={team.league_member_id} value={String(team.league_member_id)}>
                  {team.team_name} - {roster?.owner_name ?? '\u00a0'}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </CardTitle>
      </CardHeader>

      <CardContent>
        {!roster && <p className="text-sm text-muted-foreground">Choose a team above, or click its slot in the round, to see what it holds.</p>}

        {roster && (
          <>
            <ul className="space-y-0.5">
              {starters.map((slot) => (
                <SlotRow key={slot.index} slot={slot} draftId={draftId} />
              ))}
            </ul>

            <p className="mt-4 mb-1 text-xs tracking-wide text-muted-foreground uppercase">Bench</p>
            <ul className="space-y-0.5">
              {bench.map((slot) => (
                <SlotRow key={slot.index} slot={slot} draftId={draftId} />
              ))}
            </ul>
          </>
        )}
      </CardContent>
    </Card>
  );
}

function SlotRow({ draftId, slot }: { draftId: number; slot: RosterSlot }) {
  const player = slot.player;
  const label = slot.label;

  return (
    <li className={cn('flex h-12 items-center gap-2 rounded px-1 py-0.5 text-sm', !player && 'opacity-50')}>
      <span className="w-14 shrink-0 text-[10px] tracking-wide text-muted-foreground uppercase">{label == 'RB/WR/TE' ? 'Flex' : label}</span>

      {player ? (
        <>
          <PositionBadge position={player.position ?? ''} />
          <PlayerDialog draftId={draftId} name={player.full_name} playerId={player.player_id} />
          <span className="text-xs text-muted-foreground">{player.team}</span>
          <span className="ml-auto shrink-0 text-xs text-muted-foreground tabular-nums">{player.source}</span>
        </>
      ) : (
        <span className="text-sm text-muted-foreground">Empty</span>
      )}
    </li>
  );
}
