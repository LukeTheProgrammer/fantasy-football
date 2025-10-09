import { type LeagueResource } from '@/types/resources';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
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
  // const { auth } = usePage<SharedData>().props;
  // const userId = auth.user.id;
  // const userIsAdmin = isUserLeagueAdmin(league, userId);

  const draft = league.draft;

  const picks = draft?.picks.sort((a, b) => b.amount - a.amount);

  const playersDrafted = draft?.picks.filter(p => p.player_id !== null).length || 0;
  const totalPlayers = draft?.picks.length || 0;

  return (
    <div>
      <div className="mb-8 rounded-lg border bg-card">
        <div className="border-b p-6 grid grid-cols-3">
          <div className="text-left">
            <h2 className="text-lg font-semibold">Draft</h2>
            <p>{league.name} {league.season} Draft</p>
          </div>
          <div className="flex items-center justify-center">
            {playersDrafted > 0 && totalPlayers > 0 && (
              <p>{playersDrafted} / {totalPlayers} Players Drafted</p>
            )}
          </div>
          <div className="flex items-center justify-end">
            {draft.is_completed === false && (
              <Link href={route('drafts.draft-room', league.draft.id)}>
                <Button variant="outline" className="text-right">
                  Enter Draft Room
                </Button>
              </Link>
            )}
          </div>
        </div>
      </div>

      <div className="mb-8 rounded-lg border bg-card">
        <div className="border-b p-6">
          <Table>
            <TableHeader>
              <TableRow>
                {/*
                <TableHead>Round</TableHead>
                <TableHead>Pick</TableHead>
                */}
                <TableHead>$</TableHead>
                <TableHead>Player</TableHead>
                <TableHead>League Member</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {picks.map((pick) => (
                <TableRow key={pick.id}>
                  {/*
                  <TableCell>{pick.round}</TableCell>
                  <TableCell>{pick.pick_number}</TableCell>
                  */}
                  <TableCell>${parseInt(pick.amount)}</TableCell>
                  <TableCell className="flex items-center justify-start">
                    <div className="w-[4em] flex items-center justify-center">
                      {pick.player.headshot && (
                        <img src={pick.player.headshot} alt={pick.player.full_name} className="h-10" />
                      )}
                    </div>
                    <div className="pl-2">
                      <p className="font-bold">{pick?.player?.full_name}</p>
                      <p className="text-xs text-muted-foreground">
                        {pick.player.team_id} &nbsp; • &nbsp; {pick.player.position_id}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <h4 className="text-lg font-semibold">{pick.league_member.team_name}</h4>
                    <p className="text-xs text-muted-foreground">{pick.league_member.owner_name}</p>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  );
}
