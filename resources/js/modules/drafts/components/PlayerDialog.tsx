import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import type { AuctionPlayer, PlayerProfile } from '@/types/models';
import axios from 'axios';
import { useEffect, useState } from 'react';
import { money } from '../helpers/money';
import { PriceHistoryChart } from './PriceHistoryChart';

interface PlayerDialogProps {
  player: AuctionPlayer;
  draftId: number;
}

function Figure({ label, value, hint }: { label: string; value: string; hint?: string | null }) {
  return (
    <div className="rounded-lg border bg-muted/40 px-3 py-2">
      <p className="text-xs text-muted-foreground">{label}</p>
      <p className="text-lg font-semibold tabular-nums">{value}</p>
      {hint && <p className="text-[11px] text-muted-foreground">{hint}</p>}
    </div>
  );
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <section>
      <h3 className="mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">{title}</h3>
      {children}
    </section>
  );
}

/**
 * The case for and against one player, opened from his name in the room.
 *
 * The profile is fetched the first time the dialog opens and then kept, since
 * nothing in it changes fast enough to matter inside a single nomination.
 */
export function PlayerDialog({ player, draftId }: PlayerDialogProps) {
  const [open, setOpen] = useState(false);
  const [profile, setProfile] = useState<PlayerProfile | null>(null);
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    if (!open || profile) {
      return;
    }

    let current = true;

    axios
      .get<PlayerProfile>(route('drafts.players.show', [draftId, player.player_id]))
      .then((response) => current && setProfile(response.data))
      .catch(() => current && setFailed(true));

    return () => {
      current = false;
    };
  }, [open, profile, draftId, player.player_id]);

  // A new nomination reuses this component, so the last player's profile has
  // to be dropped rather than shown under the new name.
  useEffect(() => {
    setProfile(null);
    setFailed(false);
  }, [player.player_id]);

  const bio = profile?.player;
  const valuation = profile?.valuation;
  const consensus = profile?.consensus;
  const spread = consensus?.min !== null && consensus?.max !== null ? `${consensus?.min}–${consensus?.max}` : null;

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="link" className="h-auto p-0">
          <h2 className="truncate text-2xl leading-tight font-bold">{player.full_name}</h2>
        </Button>
      </DialogTrigger>

      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-[720px]">
        <DialogHeader>
          <div className="flex items-center gap-3">
            {bio?.headshot && <img src={bio.headshot} alt="" className="size-14 rounded-full bg-muted object-cover" />}
            <div>
              <DialogTitle className="flex items-center gap-2 text-2xl">
                <PositionBadge position={player.position_id} />
                {player.full_name}
              </DialogTitle>
              <DialogDescription>
                {[
                  player.team_id,
                  bio?.bye_week ? `Bye ${bio.bye_week}` : null,
                  bio?.age ? `Age ${bio.age}` : null,
                  bio?.height,
                  bio?.weight,
                  bio?.college,
                ]
                  .filter(Boolean)
                  .join(' · ')}
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        {failed && <p className="text-sm text-destructive">Could not load this player's profile.</p>}

        {!profile && !failed && <Skeleton className="h-64 w-full" />}

        {profile && (
          <div className="space-y-5">
            <div className="grid grid-cols-4 gap-2">
              <Figure
                label="Market"
                value={money(valuation?.market_value ?? null)}
                hint={valuation?.budget_share ? `${valuation.budget_share}% of a budget` : null}
              />
              <Figure label="Projected" value={money(valuation?.projected_value ?? null)} hint={`${valuation?.projected_points ?? '—'} pts`} />
              <Figure label="Overall rank" value={valuation?.rank ? `#${valuation.rank}` : '—'} hint={`Tier ${valuation?.tier ?? '—'}`} />
              <Figure
                label={`${player.position_id} left`}
                value={profile.position.rank ? `#${profile.position.rank}` : '—'}
                hint={profile.position.tier_left !== null ? `${profile.position.tier_left} more in tier` : null}
              />
            </div>

            <Section title="What this league has paid">
              <PriceHistoryChart prices={profile.prices} estimate={valuation?.market_value ?? null} season={player.season} />
            </Section>

            {consensus && (
              <Section title="Expert consensus">
                <div className="flex items-center gap-6 rounded-lg border px-4 py-3 text-sm">
                  <p>
                    <span className="text-muted-foreground">Position rank </span>
                    <span className="font-semibold tabular-nums">
                      {player.position_id}
                      {consensus.pos_rank}
                    </span>
                  </p>
                  {spread && (
                    <p>
                      <span className="text-muted-foreground">Range </span>
                      <span className="font-semibold tabular-nums">{spread}</span>
                    </p>
                  )}
                  {consensus.std !== null && (
                    <p>
                      <span className="text-muted-foreground">Disagreement </span>
                      <span className="font-semibold tabular-nums">±{consensus.std}</span>
                    </p>
                  )}
                </div>
              </Section>
            )}

            {profile.position.next.length > 0 && (
              <Section title={`If you pass: next ${player.position_id}s on the board`}>
                <ul className="divide-y rounded-lg border">
                  {profile.position.next.map((next) => (
                    <li key={next.player_id} className="flex items-center justify-between px-4 py-2 text-sm">
                      <span className="truncate">{next.full_name}</span>
                      <span className="flex shrink-0 items-center gap-4 text-muted-foreground tabular-nums">
                        <span>Tier {next.tier ?? '—'}</span>
                        <span>#{next.rank ?? '—'}</span>
                        <span className="font-semibold text-foreground">{money(next.market_value)}</span>
                      </span>
                    </li>
                  ))}
                </ul>
              </Section>
            )}
          </div>
        )}
      </DialogContent>
    </Dialog>
  );
}
