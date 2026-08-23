import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type LeagueDraftPickResource, type LeagueResource } from '@/types/resources';

interface DraftTabProps {
  league: LeagueResource;
}

export function DraftSnake({ league }: DraftTabProps) {
  const draft = league.draft;
  const picks = draft?.picks || [];
  const draftPicks: Record<string, LeagueDraftPickResource[]> = {};

  picks.forEach((pick) => {
    const roundKey = `Round ${pick.round}`;

    if (!draftPicks[roundKey]) {
      draftPicks[roundKey] = [];
    }

    draftPicks[roundKey].push(pick);
  });

  return Object.entries(draftPicks).map(([roundKey, picks]) => (
    <div key={roundKey}>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead className="text-center">Round</TableHead>
            <TableHead className="text-center">Pick</TableHead>
            <TableHead>Player</TableHead>
            <TableHead>Team</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {picks.map((pick) => (
            <TableRow key={pick.id}>
              <TableCell className="text-center">{pick.round}</TableCell>
              <TableCell className="text-center">{pick.pick_number}</TableCell>
              <TableCell>
                <div className="flex items-center justify-start">
                  <div className="flex w-[4em] items-center justify-center">
                    {pick.player.headshot && <img src={pick.player.headshot} alt={pick.player.full_name ?? undefined} className="h-8" />}
                  </div>
                  <p className="min-w-[12em] pl-2 font-bold">{pick?.player?.full_name}</p>
                  <p className="min-w-[3em] pl-2 text-muted-foreground">{pick.player.position_id}</p>
                  <p className="min-w-[3em] pl-2 text-muted-foreground">{pick.player.team_id}</p>
                </div>
              </TableCell>
              <TableCell>
                <div className="flex items-center justify-start">
                  <p className="w-[15em] font-bold">{pick.league_member.team_name}</p>
                  <p className="pl-2 text-muted-foreground">{pick.league_member.owner_name}</p>
                </div>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  ));
}
