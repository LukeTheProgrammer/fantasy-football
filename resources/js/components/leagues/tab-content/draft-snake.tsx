import { type LeagueResource } from '@/types/resources';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface DraftTabProps {
  league: LeagueResource;
}

export default function DraftTab({ league }: DraftTabProps) {
  const draft = league.draft;
  const picks = draft?.picks || [];
  const draftPicks = {};

  picks.map((pick) => {
    const roundKey = `Round ${pick.round}`;

    if (!draftPicks[roundKey]) {
      draftPicks[roundKey] = [];
    }

    draftPicks[roundKey].push(pick);
  });

  return (
    Object.entries(draftPicks).map(([roundKey, picks ]) => (
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
            {picks.map((pick: DraftPickResource) => (
              <TableRow key={pick.id}>
                <TableCell className="text-center">{pick.round}</TableCell>
                <TableCell className="text-center">{pick.pick_number}</TableCell>
                <TableCell>
                  <div className="flex items-center justify-start">
                    <div className="w-[4em] flex items-center justify-center">
                      {pick.player.headshot && (
                        <img src={pick.player.headshot} alt={pick.player.full_name} className="h-8" />
                      )}
                    </div>
                    <p className="pl-2 min-w-[12em] font-bold">{pick?.player?.full_name}</p>
                    <p className="pl-2 min-w-[3em] text-muted-foreground">{pick.player.position_id}</p>
                    <p className="pl-2 min-w-[3em] text-muted-foreground">{pick.player.team_id}</p>
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
    ))
  );
}
