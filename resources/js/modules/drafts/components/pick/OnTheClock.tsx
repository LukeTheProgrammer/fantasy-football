import { cn } from '@/common/helpers/cn';
import { Card, CardContent } from '@/components/ui/card';
import { type DraftClock, type RoundSlot } from '@/types/picks';

interface OnTheClockProps {
  clock: DraftClock;
}

/**
 * Whose pick it is, and the round it sits in. A traded pick keeps the slot it
 * was traded for, so the order is read as given rather than rotated.
 */
export function OnTheClock({ clock }: OnTheClockProps) {
  const { current, round, total } = clock;

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
          {round.map((slot) => (
            <RoundPick key={slot.overall_pick_number} slot={slot} />
          ))}
        </div>
      </CardContent>
    </Card>
  );
}

function RoundPick({ slot }: { slot: RoundSlot }) {
  return (
    <div
      className={cn(
        'flex min-w-0 flex-1 basis-0 flex-col items-center justify-center rounded-md border px-1 py-2',
        slot.is_current && 'border-primary bg-primary/10',
        slot.is_made && 'opacity-50',
      )}
    >
      <span className="text-[10px] text-muted-foreground tabular-nums">
        {slot.round}.{String(slot.pick_number).padStart(2, '0')}
      </span>
      <span className="w-full truncate text-center text-xs">{slot.team_name}</span>
    </div>
  );
}
