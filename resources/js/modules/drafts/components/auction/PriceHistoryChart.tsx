import { ChartContainer, ChartTooltip, type ChartConfig } from '@/components/ui/chart';
import type { PlayerPrice } from '@/types/models';
import { CartesianGrid, LabelList, Line, LineChart, XAxis, YAxis } from 'recharts';

interface PriceHistoryChartProps {
  prices: PlayerPrice[];
  /** This year's market estimate, drawn as the dashed last leg of the line. */
  estimate: number | null;
  season: number;
}

interface PricePoint {
  season: number;
  /** Prices actually paid; null in seasons he went undrafted. */
  paid: number | null;
  /** The dashed leg, carrying only the last paid price and this year's estimate. */
  projected: number | null;
  team: string | null;
  /** How the price compared to the biggest buy of that auction. */
  share: number | null;
}

const chartConfig = {
  paid: { label: 'Paid', color: 'var(--primary)' },
  projected: { label: 'League estimate', color: 'var(--primary)' },
} satisfies ChartConfig;

/**
 * What this league has paid for the player, season by season, with this year's
 * estimate on the end of the line.
 *
 * A line rather than bars because the trend is the point: a player who has been
 * getting cheaper every year looks cheap here before the maths says so. Seasons
 * he went undrafted break the line rather than dropping it to zero, since no
 * price is not the same as a price of nothing.
 */
export function PriceHistoryChart({ prices, estimate, season }: PriceHistoryChartProps) {
  if (prices.length === 0) {
    return <p className="text-sm text-muted-foreground">No past auctions on record for this league.</p>;
  }

  const data: PricePoint[] = [
    ...prices.map((price) => ({
      season: price.season,
      paid: price.amount,
      projected: null,
      team: price.team,
      share: price.amount && price.top ? Math.round((price.amount / price.top) * 100) : null,
    })),
    { season, paid: null, projected: estimate, team: 'League estimate', share: null },
  ];

  // The dashed leg starts at the last price actually paid, so the two lines
  // meet rather than the estimate floating on its own.
  const lastPaid = data.filter((point) => point.paid !== null).at(-1);

  if (lastPaid) {
    lastPaid.projected = lastPaid.paid;
  }

  const ceiling = Math.max(...data.map((point) => point.paid ?? point.projected ?? 0), 1);

  return (
    <ChartContainer config={chartConfig} className="aspect-auto h-44 w-full">
      <LineChart data={data} margin={{ top: 24, right: 12, left: 4, bottom: 4 }}>
        <CartesianGrid vertical={false} />
        <XAxis dataKey="season" tickLine={false} axisLine={false} tickMargin={8} />
        <YAxis
          width={38}
          tickLine={false}
          axisLine={false}
          // Headroom above the top price so the labels are not clipped.
          domain={[0, Math.ceil((ceiling * 1.15) / 5) * 5]}
          tickFormatter={(value: number) => `$${value}`}
        />
        <ChartTooltip cursor={false} content={<PriceTooltip />} />

        <Line
          dataKey="paid"
          type="linear"
          stroke="var(--color-paid)"
          strokeWidth={2}
          // Undrafted seasons leave a gap instead of being drawn through.
          connectNulls={false}
          dot={{ r: 4, fill: 'var(--color-paid)', strokeWidth: 0 }}
          activeDot={{ r: 5 }}
        >
          <LabelList dataKey="paid" position="top" offset={10} className="fill-foreground text-sm font-semibold" formatter={dollars} />
        </Line>

        <Line
          dataKey="projected"
          type="linear"
          stroke="var(--color-projected)"
          strokeWidth={2}
          strokeDasharray="5 4"
          connectNulls
          dot={{ r: 4, fill: 'var(--background)', stroke: 'var(--color-projected)', strokeWidth: 2 }}
          activeDot={{ r: 5 }}
        >
          <LabelList
            dataKey="projected"
            position="top"
            offset={10}
            className="fill-muted-foreground text-sm font-semibold"
            // Only the estimate itself is labelled; the shared point already
            // carries the price that was paid.
            formatter={(value: number) => (value === lastPaid?.paid ? '' : dollars(value))}
          />
        </Line>
      </LineChart>
    </ChartContainer>
  );
}

function dollars(value: number | null): string {
  return value === null ? '' : `$${value}`;
}

/**
 * One season: the price, who paid it, and how big a buy it was that year.
 */
function PriceTooltip({ active, payload }: { active?: boolean; payload?: { payload: PricePoint }[] }) {
  const point = payload?.[0]?.payload;

  if (!active || !point) {
    return null;
  }

  const amount = point.paid ?? point.projected;

  return (
    <div className="rounded-lg border bg-background px-3 py-2 text-xs shadow-md">
      <p className="font-medium tabular-nums">{point.season}</p>
      <p className="text-base font-semibold tabular-nums">{amount === null ? 'Undrafted' : `$${amount}`}</p>
      {point.team && <p className="text-muted-foreground">{point.team}</p>}
      {point.share !== null && <p className="text-muted-foreground tabular-nums">{point.share}% of that year's top buy</p>}
    </div>
  );
}
