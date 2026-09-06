import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type PlayerProfile, type PlayerSeason } from '@/types/picks';
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

      <DialogContent className="sm:max-w-2xl">
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
            <div className="grid grid-cols-6 gap-2">
              <Figure label="Rank" value={profile.ranking?.rank ? String(profile.ranking.rank) : '—'} />
              <Figure label="Tier" value={profile.ranking?.tier ? String(profile.ranking.tier) : '—'} />
              <Figure label="ADP" value={profile.ranking?.adp ? String(profile.ranking.adp) : '—'} />
              <Figure label="ADV" value={profile.ranking?.adv ? `$${profile.ranking.adv}` : '—'} />
              <Figure label="Proj" value={profile.projection ? String(profile.projection.points) : '—'} />
              <Figure label="PAR" value={profile.projection?.par !== null && profile.projection !== null ? String(profile.projection.par) : '—'} />
            </div>

            <SeasonTable position={profile.position} seasons={profile.seasons} />

            <dl className="w-full">
              <Detail label="Age" value={profile.age ? String(profile.age) : null} />
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

/**
 * The last few seasons, most recent first, showing only the columns his
 * position is actually judged on: nobody argues about a receiver's
 * interceptions, and the row is unreadable once it carries every stat.
 */
function SeasonTable({ position, seasons }: { position: string | null; seasons: PlayerSeason[] }) {
  if (seasons.length === 0) {
    return <p className="text-sm text-muted-foreground">No NFL seasons on record.</p>;
  }

  const pos = position?.toUpperCase() ?? '';
  const passing = pos === 'QB';
  const receiving = ['RB', 'WR', 'TE'].includes(pos);

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Year</TableHead>
          <TableHead>Tm</TableHead>
          <TableHead title="Games played">G</TableHead>
          {passing && (
            <>
              <TableHead title="Passing yards">PaYd</TableHead>
              <TableHead title="Passing touchdowns">PaTD</TableHead>
              <TableHead title="Interceptions">Int</TableHead>
            </>
          )}
          <TableHead title="Carries">Car</TableHead>
          <TableHead title="Rushing yards">RuYd</TableHead>
          <TableHead title="Rushing touchdowns">RuTD</TableHead>
          {receiving && (
            <>
              <TableHead title="Targets">Tgt</TableHead>
              <TableHead title="Receptions">Rec</TableHead>
              <TableHead title="Receiving yards">ReYd</TableHead>
              <TableHead title="Receiving touchdowns">ReTD</TableHead>
            </>
          )}
          <TableHead title="Fantasy points in this league's scoring">Pts</TableHead>
          <TableHead title="Fantasy points per game played">PPG</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {seasons.map((season) => (
          <TableRow key={season.season}>
            <TableCell className="font-medium tabular-nums">{season.season}</TableCell>
            <TableCell className="text-muted-foreground">{season.team ?? '—'}</TableCell>
            <TableCell className="tabular-nums">{season.games}</TableCell>
            {passing && (
              <>
                <TableCell className="tabular-nums">{season.passing_yards}</TableCell>
                <TableCell className="tabular-nums">{season.passing_tds}</TableCell>
                <TableCell className="tabular-nums">{season.interceptions}</TableCell>
              </>
            )}
            <TableCell className="tabular-nums">{season.rushing_carries}</TableCell>
            <TableCell className="tabular-nums">{season.rushing_yards}</TableCell>
            <TableCell className="tabular-nums">{season.rushing_tds}</TableCell>
            {receiving && (
              <>
                <TableCell className="tabular-nums">{season.targets}</TableCell>
                <TableCell className="tabular-nums">{season.receptions}</TableCell>
                <TableCell className="tabular-nums">{season.receiving_yards}</TableCell>
                <TableCell className="tabular-nums">{season.receiving_tds}</TableCell>
              </>
            )}
            <TableCell className="tabular-nums">{season.points}</TableCell>
            <TableCell className="font-medium tabular-nums">{season.points_per_game ?? '—'}</TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
