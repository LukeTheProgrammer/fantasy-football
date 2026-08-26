import { cn } from '@/common/helpers/cn';
import type { PlayerPrice } from '@/types/models';

interface PriceHistoryChartProps {
  prices: PlayerPrice[];
  /** This year's market estimate, drawn as a ghost bar on the end. */
  estimate: number | null;
  season: number;
}

/**
 * What this league has paid for the player, season by season, with this year's
 * estimate on the end of the row.
 *
 * Bars share one scale so the shape of the line is the point: a player who has
 * been getting cheaper every year looks cheap here before the maths says so.
 */
export function PriceHistoryChart({ prices, estimate, season }: PriceHistoryChartProps) {
  if (prices.length === 0) {
    return <p className="text-sm text-muted-foreground">No past auctions on record for this league.</p>;
  }

  const bars = [
    ...prices.map((price) => ({
      season: price.season,
      amount: price.amount,
      label: price.team,
      /** How the price compared to the biggest buy of that auction. */
      share: price.amount && price.top ? Math.round((price.amount / price.top) * 100) : null,
      estimated: false,
    })),
    { season, amount: estimate, label: 'Market estimate', share: null, estimated: true },
  ];

  const ceiling = Math.max(...bars.map((bar) => bar.amount ?? 0), 1);

  return (
    <div>
      <div className="flex h-40 items-end gap-3">
        {bars.map((bar) => (
          <div key={bar.season} className="flex h-full flex-1 flex-col justify-end gap-1">
            <p className={cn('text-center text-sm font-semibold tabular-nums', bar.amount === null && 'text-muted-foreground')}>
              {bar.amount === null ? '—' : `$${bar.amount}`}
            </p>
            <div
              className={cn(
                'w-full rounded-t-sm',
                bar.estimated ? 'border border-b-0 border-dashed border-primary bg-primary/15' : 'bg-primary',
                bar.amount === null && 'border border-dashed border-muted-foreground/40 bg-transparent',
              )}
              // Undrafted seasons still get a sliver so the gap is visible.
              style={{ height: `${bar.amount === null ? 2 : Math.max((bar.amount / ceiling) * 100, 4)}%` }}
              title={bar.label ?? 'Went undrafted'}
            />
          </div>
        ))}
      </div>

      <div className="mt-1 flex gap-3 border-t pt-1">
        {bars.map((bar) => (
          <div key={bar.season} className="min-w-0 flex-1 text-center">
            <p className="text-xs font-medium tabular-nums">{bar.season}</p>
            <p className="truncate text-[11px] text-muted-foreground" title={bar.label ?? undefined}>
              {bar.amount === null ? 'undrafted' : (bar.label ?? '—')}
            </p>
            {bar.share !== null && <p className="text-[11px] text-muted-foreground tabular-nums">{bar.share}% of top buy</p>}
          </div>
        ))}
      </div>
    </div>
  );
}
