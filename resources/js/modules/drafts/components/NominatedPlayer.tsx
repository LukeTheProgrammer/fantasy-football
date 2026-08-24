import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type AuctionPlayer, type AuctionTeam } from '@/types/models';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface NominatedPlayerProps {
  player: AuctionPlayer | null;
  teams: AuctionTeam[];
  draftId: number;
  onSold?: () => void;
}

function Stat({ label, value, muted = false }: { label: string; value: string | number | null; muted?: boolean }) {
  return (
    <div>
      <p className="text-xs text-muted-foreground">{label}</p>
      <p className={muted ? 'text-lg font-semibold text-muted-foreground tabular-nums' : 'text-lg font-semibold tabular-nums'}>{value ?? '—'}</p>
    </div>
  );
}

/**
 * The player currently up for bidding, with both value estimates and the form
 * that records what he actually sold for.
 */
export function NominatedPlayer({ player, teams, draftId, onSold }: NominatedPlayerProps) {
  // The page remounts this component per nomination, so form state starts
  // fresh for each player without an effect to reset it.
  const { data, setData, post, processing, errors, reset } = useForm({
    player_id: player?.player_id ?? 0,
    league_member_id: '' as string | number,
    amount: '' as string | number,
  });

  if (!player) {
    return (
      <Card>
        <CardContent className="py-10 text-center text-sm text-muted-foreground">
          Select a player from the board to put him up for bidding.
        </CardContent>
      </Card>
    );
  }

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();

    post(route('drafts.picks.store', draftId), {
      preserveScroll: true,
      onSuccess: () => {
        reset('amount');
        onSold?.();
      },
    });
  };

  const disagreement = player.market_value !== null && player.projected_value !== null ? player.projected_value - player.market_value : null;

  return (
    <Card>
      <CardContent className="space-y-4">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <h2 className="truncate text-2xl font-bold">{player.full_name}</h2>
            <div className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
              <PositionBadge position={player.position_id} />
              <span>{player.team_id}</span>
              <span>&middot;</span>
              <span>
                Rank {player.rank} &middot; Tier {player.tier ?? '—'}
              </span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 rounded-lg border bg-muted/40 p-3 md:grid-cols-3">
          <Stat label="Market est." value={player.market_value !== null ? `$${player.market_value}` : null} />
          <Stat label="Projected est." value={player.projected_value !== null ? `$${player.projected_value}` : null} />
          <Stat label={`${player.season - 1} price`} value={player.previous_price !== null ? `$${player.previous_price}` : null} muted />
        </div>

        {disagreement !== null && Math.abs(disagreement) >= 5 && (
          <p className="text-xs text-muted-foreground">
            {disagreement > 0
              ? `Projections say he is worth $${disagreement} more than this league usually pays at rank ${player.rank}.`
              : `This league usually pays $${Math.abs(disagreement)} more at rank ${player.rank} than his projection justifies.`}
          </p>
        )}

        <form onSubmit={handleSubmit} className="flex flex-wrap items-end gap-2">
          <div className="min-w-[12rem] flex-1">
            <label className="mb-1 block text-xs text-muted-foreground">Sold to</label>
            <Select value={String(data.league_member_id)} onValueChange={(value) => setData('league_member_id', value)}>
              <SelectTrigger>
                <SelectValue placeholder="Select team" />
              </SelectTrigger>
              <SelectContent>
                {teams.map((team) => (
                  <SelectItem key={team.id} value={String(team.id)} disabled={team.open_spots === 0}>
                    {team.team_name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="w-28">
            <label className="mb-1 block text-xs text-muted-foreground">Amount</label>
            <Input
              type="number"
              min={1}
              inputMode="numeric"
              placeholder="$"
              value={data.amount}
              onChange={(event) => setData('amount', event.target.value)}
            />
          </div>

          <Button type="submit" disabled={processing || !data.league_member_id || !data.amount}>
            Sold
          </Button>
        </form>

        {Object.values(errors).length > 0 && <p className="text-sm text-destructive">{Object.values(errors)[0]}</p>}
      </CardContent>
    </Card>
  );
}
