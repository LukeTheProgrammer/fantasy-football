import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type DraftClock, type RoundSlot } from '@/types/picks';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { useEffect, useState } from 'react';

interface OnTheClockProps {
  clock: DraftClock;
}

/**
 * Whose pick it is, and the round beside it. A traded pick keeps the slot it
 * was traded for, so the order is read as given rather than rotated.
 *
 * The round on show can be paged away from the live one to look back at what
 * went, or ahead at what is coming, without the clock itself moving.
 */
export function OnTheClock({ clock }: OnTheClockProps) {
  const { current, current_round: currentRound, rounds, total } = clock;

  const [visibleRound, setVisibleRound] = useState(currentRound);

  // A pick can carry the draft into the next round, and the board should
  // follow it there rather than sit on the round that just closed.
  useEffect(() => setVisibleRound(currentRound), [currentRound]);

  if (!current) {
    return (
      <Card>
        <CardContent className="py-6 text-center">
          <p className="text-xl font-semibold">The draft is complete.</p>
          <p className="text-sm text-muted-foreground">All {total} picks are in.</p>
        </CardContent>
      </Card>
    );
  }

  const slots = rounds[visibleRound - 1] ?? [];

  return (
    <Card className="py-4">
      <CardContent className="flex items-stretch gap-6">
        <div className="flex shrink-0 flex-col items-center justify-center rounded-md border-2 p-4">
          <p className="text-xs tracking-wide text-muted-foreground uppercase">On the clock</p>
          <p className="text-3xl font-bold">{current.team_name}</p>
          <p className="text-sm text-muted-foreground">{current.owner_name}</p>
        </div>

        {/* Every slot in the round takes an equal share of what is left of the
            row: basis-0 so the widths come from the share rather than from the
            length of a team's name, min-w-0 so a long one truncates instead of
            pushing its neighbours out. */}
        <div className="flex min-w-0 flex-1 items-stretch gap-2">
          {slots.map((slot) => (
            <RoundPick key={slot.overall_pick_number} slot={slot} />
          ))}
        </div>

        <div className="flex shrink-0 flex-col justify-center gap-1">
          <Button
            size="icon"
            variant="outline"
            aria-label="Previous round"
            disabled={visibleRound <= 1}
            onClick={() => setVisibleRound((round) => Math.max(1, round - 1))}
          >
            <ChevronUp />
          </Button>

          <span className="text-center text-xs text-muted-foreground tabular-nums">R{visibleRound}</span>

          <Button
            size="icon"
            variant="outline"
            aria-label="Next round"
            disabled={visibleRound >= rounds.length}
            onClick={() => setVisibleRound((round) => Math.min(rounds.length, round + 1))}
          >
            <ChevronDown />
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}

function RoundPick({ slot }: { slot: RoundSlot }) {
  const player = slot.player;

  return (
    <div
      className={cn(
        'flex min-w-0 flex-1 basis-0 flex-col items-center justify-center gap-1 rounded-md border px-1 py-2',
        slot.is_current && 'border-2 border-primary bg-primary/10',
        slot.is_made && !slot.is_current && 'opacity-60',
      )}
    >
      <span className="text-[10px] text-muted-foreground tabular-nums">
        {slot.round}.{String(slot.pick_number).padStart(2, '0')}
      </span>

      {player?.headshot && <img src={player.headshot} alt="" className="size-8 rounded-full bg-muted object-cover" />}

      <span className="w-full truncate text-center text-xs">{player ? player.full_name : slot.team_name}</span>

      {player && <span className="w-full truncate text-center text-[10px] text-muted-foreground">{slot.team_name}</span>}
    </div>
  );
}
