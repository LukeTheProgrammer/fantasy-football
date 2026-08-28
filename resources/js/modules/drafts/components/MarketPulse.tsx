import { cn } from '@/common/helpers/cn';
import { Card, CardContent } from '@/components/ui/card';
import { type AuctionMarket } from '@/types/models';

interface MarketPulseProps {
  market: AuctionMarket;
  /** Players still on the board, for the count beside the picks. */
  availableCount: number;
}

/**
 * How hot the room is running.
 *
 * Inflation is the difference between what the league has paid and what the
 * board marked those same players at. Positive means the money is going faster
 * than the value, so everything still to come has to go for less than it says
 * on the board; negative means there are bargains left to take.
 */
export function MarketPulse({ market, availableCount }: MarketPulseProps) {
  const { inflation } = market;

  const hot = inflation !== null && inflation > 0;
  const marketPerc = (market.spent / market.expected).toFixed(0);
  const moneyPerc = (market.money_left / market.value_left).toFixed(0);
  const playerPerc = (market.picks / availableCount).toFixed(0);

  return (
    <div className="flex items-stretch gap-2">
      <Inflation inflation={inflation} hot={hot} />
      <Figure label="Spent" value={`$${market.spent} / $${market.expected}`} sub={`${marketPerc}%`} />
      <Figure label="Money / Value" value={`$${market.money_left} / $${market.value_left}`} sub={`${moneyPerc}%`} />
      <Figure
        label="Players"
        value={`${market.picks} / ${availableCount}`}
        sub={`${playerPerc}%`}
        className="text-sm font-medium text-muted-foreground"
      />
    </div>
  );
}

function Figure({ label, value, sub, className }: { label: string; value: string; sub?: string; className?: string }) {
  return (
    <Card className="py-4">
      <CardContent className="text-center">
        <p className="text-[10px] leading-tight whitespace-nowrap text-muted-foreground">{label}</p>
        <p className={cn('py-1 text-base leading-tight font-semibold tabular-nums', className)}>{value}</p>
        {sub && <p className="text-[10px] leading-tight whitespace-nowrap text-muted-foreground">{sub}</p>}
      </CardContent>
    </Card>
  );
}

function Inflation({ inflation, hot }: { inflation: number | null; hot: boolean }) {
  const inflationClass = [
    'text-xl font-bold',
    inflation === null ? 'text-muted-foreground' : null,
    hot ? ' text-destructive' : 'text-emerald-600 dark:text-emerald-500',
  ];

  const formatted = inflation === null ? '-' : `${inflation}%`;

  return <Figure label="Inflation" value={`${hot ? '+' : ''}${formatted}`} className={cn(inflationClass)} />;
}
