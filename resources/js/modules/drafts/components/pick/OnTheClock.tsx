import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type DraftClock, type RoundSlot } from '@/types/picks';
import { ChevronDown, ChevronUp, X } from 'lucide-react';
import { useEffect, useState } from 'react';

interface OnTheClockProps {
  /** Whether the viewer may undo a pick. */
  canRecord: boolean;
  clock: DraftClock;
  onUndo: (pickId: number) => void;
}

/**
 * Whose pick it is, and the round beside it. A traded pick keeps the slot it
 * was traded for, so the order is read as given rather than rotated.
 *
 * The round on show can be paged away from the live one to look back at what
 * went, or ahead at what is coming, without the clock itself moving.
 */
export function OnTheClock({ canRecord, clock, onUndo }: OnTheClockProps) {
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
        {/* Every slot in the round takes an equal share of what is left of the
            row: basis-0 so the widths come from the share rather than from the
            length of a team's name, min-w-0 so a long one truncates instead of
            pushing its neighbours out. */}
        <div className="flex min-w-0 flex-1 items-stretch gap-2">
          {slots.map((slot) => (
            <RoundPick key={slot.overall_pick_number} slot={slot} canRecord={canRecord} onUndo={onUndo} />
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

interface RoundPickProps {
  canRecord: boolean;
  onUndo: (pickId: number) => void;
  slot: RoundSlot;
}

function RoundPick({ canRecord, onUndo, slot }: RoundPickProps) {
  const player = slot.player;
  // slot.is_made && !slot.is_current && 'opacity-60',

  const pickLabel = `${slot.round}.${String(slot.pick_number).padStart(2, '0')}`;

  // A pick is undone from the board itself, so the one to correct is the one
  // already on screen. Confirmed first: undoing puts that slot back on the
  // clock, and a mis-click mid draft costs more than a moment's pause.
  const undo = () => {
    if (!player?.pick_id) {
      return;
    }

    if (window.confirm(`Undo ${player.full_name ?? 'this pick'} at ${pickLabel}?`)) {
      onUndo(player.pick_id);
    }
  };

  return (
    <div
      className={cn(
        'group relative flex h-40 min-w-0 flex-1 basis-0 flex-col items-center justify-center gap-1 rounded-md border px-1 py-2',
        slot.is_current && 'border-2 border-primary bg-primary/10',
      )}
    >
      {player && canRecord && (
        <button
          type="button"
          onClick={undo}
          aria-label={`Undo pick ${pickLabel}`}
          className="absolute top-0.5 right-0.5 rounded-full bg-background/80 p-0.5 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:text-destructive focus-visible:opacity-100"
        >
          <X className="size-3" />
        </button>
      )}

      <span className="text-[10px] text-muted-foreground tabular-nums">
        {slot.round}.{String(slot.pick_number).padStart(2, '0')}
      </span>
      <span className="w-full truncate text-center text-xs">{slot.team_name}</span>

      {player?.headshot ? (
        <img src={player.headshot} alt="" className="size-20 rounded-full bg-muted object-cover" />
      ) : (
        <div className="h-20">&nbsp;</div>
      )}
      {player ? (
        <span className="w-full truncate text-center text-[10px] text-muted-foreground">{player.full_name}</span>
      ) : (
        <div className="h-4">&nbsp;</div>
      )}
    </div>
  );
}
