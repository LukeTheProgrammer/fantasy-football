import { cn } from '@/common/helpers/cn';
import { type AuctionMarket } from '@/types/models';

interface MarketPulseProps {
  market: AuctionMarket;
  /** Players still on the board, for the count beside the picks. */
  availableCount: number;
}

function Figure({ label, value, className }: { label: string; value: string; className?: string }) {
  return (
    <div className="text-right">
      <p className="text-[10px] leading-tight whitespace-nowrap text-muted-foreground uppercase">{label}</p>
      <p className={cn('text-base leading-tight font-semibold tabular-nums', className)}>{value}</p>
    </div>
  );
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

  return (
    <div className="flex items-center gap-5">
      <div className="text-right">
        <p className="text-[10px] leading-tight text-muted-foreground uppercase">Inflation</p>
        <p
          className={cn(
            'text-xl leading-tight font-bold tabular-nums',
            inflation === null ? 'text-muted-foreground' : hot ? 'text-destructive' : 'text-emerald-600 dark:text-emerald-500',
          )}
          title="What the league has paid for the players already sold, against what the board marked them at."
        >
          {inflation === null ? '—' : `${inflation > 0 ? '+' : ''}${inflation}%`}
        </p>
      </div>

      <Figure label="Spent" value={`$${market.spent} / $${market.expected}`} />
      <Figure label="Money left" value={`$${market.money_left}`} />
      <Figure label="Value left" value={`$${market.value_left}`} />
      <Figure label="Board" value={`${market.picks} picks · ${availableCount} left`} className="text-sm font-medium text-muted-foreground" />
    </div>
  );
}
