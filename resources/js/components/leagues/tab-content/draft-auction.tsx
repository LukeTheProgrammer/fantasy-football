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
  const draftPicks = [...picks].sort((a, b) => b.amount - a.amount);

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>$</TableHead>
          <TableHead>Player</TableHead>
          <TableHead>Team</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {draftPicks.map((pick) => (
          <TableRow key={pick.id}>
            <TableCell>${parseInt(pick.amount)}</TableCell>
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
  );
}
