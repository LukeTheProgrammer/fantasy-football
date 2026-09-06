import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DraftClock, RoundSlot, RoundSlotPlayer } from '@/types/picks';
import { ChevronDown, ChevronUp, UserRound, X } from 'lucide-react';
import { useEffect, useState } from 'react';

interface OnTheClockProps {
  /** Whether the viewer may undo a pick. */
  canRecord: boolean;
  clock: DraftClock;
  /** Show a team's roster, named by the slot that belongs to it. */
  onSelectTeam: (leagueMemberId: number) => void;
  onSkip: () => void;
  onUndo: (pickId: number) => void;
}

/**
 * Whose pick it is, and the round beside it. A traded pick keeps the slot it
 * was traded for, so the order is read as given rather than rotated.
 *
 * The round on show can be paged away from the live one to look back at what
 * went, or ahead at what is coming, without the clock itself moving.
 *
 * A slot is also how a team's roster is opened, because the round is already
 * the one place on the board where every team is named.
 */
export function OnTheClock({ canRecord, clock, onSelectTeam, onSkip, onUndo }: OnTheClockProps) {
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
            <RoundPick key={slot.overall_pick_number} canRecord={canRecord} onSelectTeam={onSelectTeam} onSkip={onSkip} onUndo={onUndo} slot={slot} />
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
  onSelectTeam: (leagueMemberId: number) => void;
  onSkip: () => void;
  onUndo: (pickId: number) => void;
  slot: RoundSlot;
}

function RoundPick({ canRecord, onSelectTeam, onSkip, onUndo, slot }: RoundPickProps) {
  const player = slot.player;

  const pickLabel = `${slot.round}.${String(slot.pick_number).padStart(2, '0')}`;

  // A pick is undone from the board itself, so the one to correct is the one
  // already on screen. Confirmed first: undoing puts that slot back on the
  // clock, and a mis-click mid draft costs more than a moment's pause.
  const undo = () => {
    if (slot.pick_id === null) {
      return;
    }

    const what = slot.is_skipped ? `the skip at ${pickLabel}` : `${player?.full_name ?? 'this pick'} at ${pickLabel}`;

    if (window.confirm(`Undo ${what}?`)) {
      onUndo(slot.pick_id);
    }
  };

  // Passing costs the slot, so it is confirmed the same way undoing one is.
  const skip = () => {
    if (window.confirm(`Skip ${slot.team_name ?? 'this team'} at ${pickLabel}? The slot is given up and nobody is taken.`)) {
      onSkip();
    }
  };

  // The undo control sits over the slot rather than inside it, because the
  // slot is itself a button now and a button cannot hold another one.
  return (
    <div className="group relative flex h-40 min-w-0 flex-1 basis-0">
      <button
        type="button"
        disabled={slot.league_member_id === null}
        aria-label={`Show the roster for ${slot.team_name ?? pickLabel}`}
        onClick={() => slot.league_member_id !== null && onSelectTeam(slot.league_member_id)}
        className={cn(
          'flex w-full min-w-0 flex-col items-center justify-center gap-1 rounded-md border px-1 py-2 transition-colors',
          'enabled:hover:border-primary/60',
          slot.is_current && 'border-2 border-primary bg-primary/10',
        )}
      >
        <span className="text-[10px] text-muted-foreground tabular-nums">{pickLabel}</span>
        <span className="w-full truncate text-center text-xs">{slot.team_name}</span>

        <Player player={player} isSkipped={slot.is_skipped} />
      </button>

      {slot.pick_id !== null && canRecord && <UndoButton label={pickLabel} undo={undo} />}

      {slot.is_current && canRecord && <SkipButton onSkip={skip} />}
    </div>
  );
}

/**
 * Gives up the slot on the clock.
 *
 * Only ever offered on the live slot, because a slot further down the order
 * cannot be reached without passing the ones in front of it first.
 */
function SkipButton({ onSkip }: { onSkip: () => void }) {
  const classes = [
    'absolute',
    'bottom-0.5',
    'left-1/2',
    '-translate-x-1/2',
    'rounded',
    'border',
    'bg-background/90',
    'px-1.5',
    'py-0.5',
    'text-[9px]',
    'tracking-wide',
    'text-muted-foreground',
    'uppercase',
    'opacity-0',
    'transition-opacity',
    'group-hover:opacity-100',
    'hover:text-destructive',
    'focus-visible:opacity-100',
  ];

  return (
    <button type="button" onClick={onSkip} aria-label="Skip this pick" className={classes.join(' ')}>
      Skip
    </button>
  );
}

function UndoButton({ label, undo }: { label: string; undo: () => void }) {
  const classes = [
    'absolute',
    'top-0.5',
    'right-0.5',
    'rounded-full',
    'bg-background/80',
    'p-0.5',
    'text-muted-foreground',
    'opacity-0',
    'transition-opacity',
    'group-hover:opacity-100',
    'hover:text-destructive',
    'focus-visible:opacity-100',
  ];

  return (
    <button type="button" onClick={undo} aria-label={`Undo pick ${label}`} className={classes.join(' ')}>
      <X className="size-3" />
    </button>
  );
}

function Player({ isSkipped, player }: { isSkipped: boolean; player: RoundSlotPlayer | null }) {
  if (!player) {
    return (
      <>
        <div className="flex h-20 items-center justify-center">
          <UserRound size="64" className={isSkipped ? 'text-muted-foreground/20' : 'text-muted-foreground/40'} />
        </div>
        <div className="h-4 text-[10px] tracking-wide text-muted-foreground uppercase">{isSkipped ? 'Skipped' : '\u00a0'}</div>
      </>
    );
  }

  return (
    <>
      {player?.headshot ? (
        <img src={player.headshot} alt="" className="size-20 rounded-full bg-muted object-cover" />
      ) : (
        <div className="h-20">
          <UserRound />
        </div>
      )}
      <span className="w-full truncate text-center text-[10px] text-muted-foreground">{player.full_name}</span>
      <span className="w-full truncate text-center text-[8px] text-muted-foreground">
        {player.position} &nbsp; {player.team}
      </span>
    </>
  );
}
