import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type PlayerProfile } from '@/types/picks';
import axios from 'axios';
import { useEffect, useState } from 'react';

interface PlayerDialogProps {
  draftId: number;
  name: string | null;
  playerId: number | null;
}

/**
 * The little worth knowing about one player, opened from his name.
 *
 * The profile is fetched the first time the dialog opens and then kept: none
 * of it changes fast enough to matter inside a draft.
 */
export function PlayerDialog({ draftId, name, playerId }: PlayerDialogProps) {
  const [open, setOpen] = useState(false);
  const [profile, setProfile] = useState<PlayerProfile | null>(null);
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    if (!open || profile || playerId === null) {
      return;
    }

    let current = true;

    axios
      .get<PlayerProfile>(route('drafts.board-players.show', [draftId, playerId]))
      .then((response) => current && setProfile(response.data))
      .catch(() => current && setFailed(true));

    return () => {
      current = false;
    };
  }, [open, profile, draftId, playerId]);

  if (playerId === null) {
    return <span className="truncate">{name}</span>;
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <button type="button" className="truncate text-left hover:underline">
          {name}
        </button>
      </DialogTrigger>

      <DialogContent>
        <DialogHeader>
          <DialogTitle className="flex items-center gap-3">
            {profile?.headshot && <img src={profile.headshot} alt="" className="size-12 rounded-full bg-muted object-cover" />}
            {profile?.position && <PositionBadge position={profile.position} />}
            <span className="min-w-0">
              <span className="block truncate">{profile?.full_name ?? name}</span>
              <span className="flex items-center gap-2 text-sm font-normal text-muted-foreground">
                {profile?.team}
                {profile?.jersey ? ` # ${profile.jersey}` : null}
              </span>
            </span>
          </DialogTitle>
        </DialogHeader>

        {failed && <p className="text-sm text-muted-foreground">That player could not be loaded.</p>}

        {!profile && !failed && (
          <div className="space-y-2">
            <Skeleton className="h-16 w-full" />
            <Skeleton className="h-10 w-full" />
          </div>
        )}

        {profile && (
          <div className="space-y-4">
            <div className="grid grid-cols-4 gap-2">
              <Figure label="Rank" value={profile.ranking?.rank ? String(profile.ranking.rank) : '—'} />
              <Figure label="Tier" value={profile.ranking?.tier ? String(profile.ranking.tier) : '—'} />
              <Figure label="ADP" value={profile.ranking?.adp ?? '—'} />
              <Figure label="ADV" value={profile.ranking?.adv ? `$${profile.ranking.adv}` : '—'} />
            </div>

            <dl className="w-full">
              <Detail label="Age" value={profile.age ? String(profile.age) : null} />
              <Detail label="College" value={profile.college} />
              <Detail label="Height" value={profile.height} />
              <Detail label="Weight" value={profile.weight} />
              <Detail label="Team" value={profile.owner ? `${profile.owner.team_name} (${profile.owner.source})` : 'Undrafted'} />
            </dl>
          </div>
        )}
      </DialogContent>
    </Dialog>
  );
}

function Figure({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-lg border bg-muted/40 px-3 py-2">
      <p className="text-xs text-muted-foreground">{label}</p>
      <p className="text-lg font-semibold tabular-nums">{value}</p>
    </div>
  );
}

function Detail({ label, value }: { label: string; value: string | null }) {
  if (!value) {
    return null;
  }

  return (
    <div className="flex items-center justify-between gap-2">
      <dt className="text-muted-foreground">{label}</dt>
      <dd>{value}</dd>
    </div>
  );
}
