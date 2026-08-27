import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';
import { type DraftPick } from '@/types/models';
import { useMemo } from 'react';
import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from 'recharts';

interface PositionPriceChartProps {
  picks: DraftPick[];
}

/** Only five chart colours exist, so positions past the fifth reuse them. */
const CHART_COLORS = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];

/** The order positions are worth reading in, with anything else after. */
const POSITION_ORDER = ['QB', 'RB', 'WR', 'TE', 'K', 'DST', 'D/ST'];

/**
 * What each position cost, most expensive first.
 *
 * One curve per position, plotted against rank within that position rather than
 * against pick number: the shape is the point, showing where a position's
 * prices fall off a cliff and where they stay flat, which is what says whether
 * waiting on it is cheap or expensive.
 */
export function PositionPriceChart({ picks }: PositionPriceChartProps) {
  const { data, series, config } = useMemo(() => {
    const byPosition = new Map<string, number[]>();

    for (const pick of picks) {
      const position = pick.player?.position_id;
      const amount = Number(pick.amount);

      if (!position || !Number.isFinite(amount) || amount <= 0) {
        continue;
      }

      byPosition.set(position, [...(byPosition.get(position) ?? []), amount]);
    }

    const positions = [...byPosition.keys()].sort((a, b) => {
      const rankA = POSITION_ORDER.indexOf(a);
      const rankB = POSITION_ORDER.indexOf(b);

      return (rankA === -1 ? POSITION_ORDER.length : rankA) - (rankB === -1 ? POSITION_ORDER.length : rankB) || a.localeCompare(b);
    });

    for (const prices of byPosition.values()) {
      prices.sort((a, b) => b - a);
    }

    const deepest = Math.max(0, ...[...byPosition.values()].map((prices) => prices.length));

    // One row per rank. A position with fewer players drafted than the deepest
    // one leaves the rest of its row undefined, so its curve stops rather than
    // running along the floor at zero.
    const data = Array.from({ length: deepest }, (_, index) => {
      const row: Record<string, number | string> = { rank: index + 1 };

      for (const position of positions) {
        const price = byPosition.get(position)?.[index];

        if (price !== undefined) {
          row[position.replace(/[^A-Za-z0-9]/g, '')] = price;
        }
      }

      return row;
    });

    // Chart colours become CSS variables named after the key, so the key has to
    // be a legal variable name: "D/ST" is not.
    const series = positions.map((position) => ({ position, key: position.replace(/[^A-Za-z0-9]/g, '') }));

    const config = Object.fromEntries(
      series.map(({ position, key }, index) => [key, { label: position, color: CHART_COLORS[index % CHART_COLORS.length] }]),
    ) satisfies ChartConfig;

    return { data, series, config };
  }, [picks]);

  if (data.length === 0) {
    return <p className="text-sm text-muted-foreground">No prices were recorded for this draft.</p>;
  }

  return (
    <ChartContainer config={config} className="aspect-auto h-80 w-full">
      <AreaChart data={data} margin={{ top: 12, right: 12, left: 4, bottom: 4 }}>
        <CartesianGrid vertical={false} />
        <XAxis
          dataKey="rank"
          tickLine={false}
          axisLine={false}
          tickMargin={8}
          // The nth most expensive player at the position.
          tickFormatter={(value: number) => `#${value}`}
        />
        <YAxis width={44} tickLine={false} axisLine={false} tickMargin={8} tickFormatter={(value: number) => `$${value}`} />
        <ChartTooltip
          cursor={false}
          content={<ChartTooltipContent labelFormatter={(label) => `Position rank #${label}`} formatter={priceRow} indicator="dot" />}
        />
        <defs>
          {series.map(({ key }) => (
            <linearGradient key={key} id={`fill-${key}`} x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={`var(--color-${key})`} stopOpacity={0.6} />
              <stop offset="95%" stopColor={`var(--color-${key})`} stopOpacity={0.05} />
            </linearGradient>
          ))}
        </defs>
        {/* Curves are read on top of each other rather than summed: stacking
            them would show a total nobody paid. */}
        {series.map(({ key }) => (
          <Area
            key={key}
            dataKey={key}
            type="natural"
            fill={`url(#fill-${key})`}
            stroke={`var(--color-${key})`}
            strokeWidth={2}
            connectNulls={false}
            dot={false}
          />
        ))}
        <ChartLegend content={<ChartLegendContent />} />
      </AreaChart>
    </ChartContainer>
  );
}

/**
 * One position's price on this row, as dollars.
 */
function priceRow(value: unknown, name: unknown) {
  return (
    <div className="flex w-full items-center justify-between gap-4">
      <span className="text-muted-foreground">{String(name)}</span>
      <span className="font-medium tabular-nums">${Number(value)}</span>
    </div>
  );
}
