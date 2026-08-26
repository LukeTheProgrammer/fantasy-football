import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type AuctionPlayer, type AuctionTeam } from '@/types/models';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { money } from '../helpers/money';
import { PlayerDialog } from './PlayerDialog';

interface NominatedPlayerProps {
  player: AuctionPlayer | null;
  teams: AuctionTeam[];
  draftId: number;
  onPicked?: () => void;
}

function Stat({ label, value, muted = false }: { label: string; value: string | number | null; muted?: boolean }) {
  return (
    <div className="text-center">
      <p className="text-xs whitespace-nowrap text-muted-foreground">{label}</p>
      <p className={muted ? 'text-xl font-semibold text-muted-foreground tabular-nums' : 'text-xl font-semibold tabular-nums'}>{value ?? '—'}</p>
    </div>
  );
}

/**
 * The player currently up for bidding, with both value estimates and the form
 * that records what he actually went for.
 *
 * Laid out as a bar across the top of the room: identity, then the numbers,
 * then the pick, reading left to right in the order the auction happens.
 */
export function NominatedPlayer({ player, teams, draftId, onPicked }: NominatedPlayerProps) {
  // The page remounts this component per nomination, so form state starts
  // fresh for each player without an effect to reset it.
  const { data, setData, post, processing, errors, reset } = useForm({
    player_id: player?.player_id ?? 0,
    league_member_id: '' as string | number,
    amount: '' as string | number,
  });

  if (!player) {
    return (
      <Card className="h-full">
        <CardContent className="flex h-full items-center justify-center py-6 text-sm text-muted-foreground">
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
        onPicked?.();
      },
    });
  };

  const disagreement = player.market_value !== null && player.projected_value !== null ? player.projected_value - player.market_value : null;

  return (
    <Card className="h-full">
      <CardContent className="grid h-full grid-cols-3 gap-2">
        <div className="">
          <div className="flex items-start justify-start gap-3">
            {player.headshot && <img src={player.headshot} alt="" className="size-14 shrink-0 rounded-full bg-muted object-cover" />}
            <div className="pt-1">
              <PositionBadge position={player.position_id} />
            </div>
            <div>
              <PlayerDialog player={player} draftId={draftId} />
              <div className="mt-1 flex items-center justify-start gap-4 text-sm text-muted-foreground">
                <span>{player.team_id}</span>
                <span>Rank {player.rank}</span>
                <span>Tier {player.tier ?? '—'}</span>
              </div>
            </div>
          </div>
        </div>

        <div>
          <div className="flex items-center justify-between rounded-lg border bg-muted/40 px-4 py-2">
            <Stat label="League" value={player.market_value !== null ? `$${player.market_value}` : null} />
            <Stat label="VAR" value={player.projected_value !== null ? `$${player.projected_value}` : null} />
            <Stat label="Diff" value={money(disagreement)} muted />
          </div>
        </div>

        <div className="flex justify-end">
          <form onSubmit={handleSubmit} className="flex items-center gap-2">
            <div className="w-48">
              <label className="sr-only">Picked by</label>
              <Select value={String(data.league_member_id)} onValueChange={(value) => setData('league_member_id', value)}>
                <SelectTrigger className="w-full">
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

            <div className="w-24">
              <label className="sr-only">Amount</label>
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
              Pick
            </Button>
          </form>
          {Object.values(errors).length > 0 && <p className="shrink-0 text-sm text-destructive">{Object.values(errors)[0]}</p>}
        </div>
      </CardContent>
    </Card>
  );
}
