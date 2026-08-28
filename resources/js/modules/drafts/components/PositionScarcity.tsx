import { cn } from '@/common/helpers/cn';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type MarketPosition } from '@/types/models';

interface PositionScarcityProps {
  positions: MarketPosition[];
  /** The position the board is filtered to, highlighted here to match. */
  active: string | null;
  onSelect: (position: string | null) => void;
}

/**
 * Where the pressure is, one row per position.
 *
 * Two numbers decide whether to bid now or wait. How many players are left in
 * the tier at the top of the position — once that is one or two, the price
 * steps up behind them — and how much money is still held by teams that have
 * not filled the spot yet. A position no one needs any more cannot be bid up,
 * whatever the board says it is worth.
 */
export function PositionScarcity({ positions, active, onSelect }: PositionScarcityProps) {
  return (
    <div className="rounded-lg border bg-card">
      <div className="grid grid-cols-[auto_1fr_1fr_1fr] items-center gap-x-2 px-2 pt-1.5 pb-1 text-[9px] text-muted-foreground uppercase">
        <span>Pos</span>
        <span className="text-right" title="Players left in the tier at the top of the position">
          Tier
        </span>
        <span className="text-right" title="Unfilled starting spots across the league">
          Need
        </span>
        <span className="text-right" title="Money held by teams that still need one">
          Chasing
        </span>
      </div>

      {positions.map((position) => {
        // One or two left in the top tier is the last chance to buy at this
        // price, so it is called out rather than left to be read.
        const thin = position.top_tier_left > 0 && position.top_tier_left <= 2;

        return (
          <button
            key={position.position}
            type="button"
            onClick={() => onSelect(active === position.position ? null : position.position)}
            className={cn(
              'grid w-full grid-cols-[auto_1fr_1fr_1fr] items-center gap-x-2 border-t px-2 py-1 text-left transition-colors hover:bg-muted/60',
              active === position.position && 'bg-muted',
              position.slots_open === 0 && 'opacity-50',
            )}
          >
            <PositionBadge position={position.position} />
            <span className={cn('text-right text-xs tabular-nums', thin && 'font-bold text-destructive')}>
              {position.top_tier === null ? '—' : `${position.top_tier_left} @ T${position.top_tier}`}
            </span>
            <span className="text-right text-xs tabular-nums">
              {position.slots_open}
              <span className="text-[10px] text-muted-foreground"> / {position.teams_needing}t</span>
            </span>
            <span className="text-right text-xs tabular-nums">${position.money_chasing}</span>
          </button>
        );
      })}
    </div>
  );
}
