import { Card, CardContent } from '@/components/ui/card';
import { type ClockSlot, type DraftClock } from '@/types/picks';

interface OnTheClockProps {
  clock: DraftClock;
}

/**
 * Whose pick it is, and who is waiting. A traded pick keeps the slot it was
 * traded for, so the order is read as given rather than rotated.
 */
export function OnTheClock({ clock }: OnTheClockProps) {
  const { current, upcoming, made, total, remaining } = clock;

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
    <Card>
      <CardContent className="flex flex-wrap items-center justify-between gap-6 py-6">
        <div>
          <p className="text-xs tracking-wide text-muted-foreground uppercase">On the clock</p>
          <p className="text-3xl font-bold">{current.team_name}</p>
          <p className="text-sm text-muted-foreground">{current.owner_name}</p>
        </div>

        <div className="text-center">
          <p className="text-xs tracking-wide text-muted-foreground uppercase">Pick</p>
          <p className="text-3xl font-bold">
            {current.round}.{String(current.pick_number).padStart(2, '0')}
          </p>
          <p className="text-sm text-muted-foreground">
            {made} of {total} made &middot; {remaining} left
          </p>
        </div>

        <div className="min-w-0 flex-1">
          <p className="mb-2 text-xs tracking-wide text-muted-foreground uppercase">Up next</p>
          <div className="flex flex-wrap gap-2">
            {upcoming.map((slot: ClockSlot) => (
              <span key={slot.overall_pick_number} className="rounded-md border px-2 py-1 text-xs whitespace-nowrap">
                <span className="text-muted-foreground">
                  {slot.round}.{String(slot.pick_number).padStart(2, '0')}
                </span>{' '}
                {slot.team_name}
              </span>
            ))}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
