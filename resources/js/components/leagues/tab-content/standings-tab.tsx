import TeamAvatar from '@/components/leagues/team-avatar';
import { useMemo } from 'react';
import { type LeagueResource, type LeagueMemberResource } from '@/types/resources';
import { rankName } from '@/lib/utils';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface StandingsTabProps {
  league: LeagueResource;
}

interface Standing {
  memberId: string;
  member: LeagueMemberResource;
  pointsFor: number;
  pointsAgainst: number;
  pfRank: string;
  paRank: string;
  record: string;
}

export default function StandingsTab({ league }: StandingsTabProps) {
  const { standings } = useMemo(() => {
    const standings: Standing[] = [];

    league.members
      .sort((a: LeagueMemberResource, b: LeagueMemberResource) => b.wins - a.wins)
      .forEach((m: LeagueMemberResource) => {
        const standing: Standing = {
          memberId: m.id,
          member: m,
          pointsFor: m.points_for,
          pointsAgainst: m.points_against,
          pfRank: m.points_for_rank,
          paRank: m.points_against_rank,
          record: `${m.wins} - ${m.losses} - ${m.ties}`,
        };

        standings.push(standing);
      });
    return { standings };
  }, [league.members]);

  return (
    <div className="w-full p-4 mb-8 rounded-lg border bg-card">

      <div className="w-full flex items-center justify-start mb-6">
        <h4 className="text-lg font-semibold">Standings</h4>
      </div>

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>RK</TableHead>
            <TableHead>Team</TableHead>
            <TableHead>Points For</TableHead>
            <TableHead>Points Against</TableHead>
            <TableHead>Record</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {standings.map((standings, k) => (
            <TableRow key={k}>
              <TableCell>
                {rankName(k + 1)}
              </TableCell>
              <TableCell>
                <div className="flex items-center justify-start space-x-2">
                  <TeamAvatar member={standings.member} />
                  <div className="ml-2">
                    <span className="text-lg font-bold">{standings.member.team_name}</span>
                    <span className="text-xs text-muted-foreground pl-2">{standings.member.owner_name}</span>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <span className="text-lg font-extrabold">{standings.pointsFor}</span>
                <span className="text-xs text-muted-foreground pl-2"> ({standings.pfRank})</span>
              </TableCell>
              <TableCell>
                <span className="text-lg font-extrabold">{standings.pointsAgainst}</span>
                <span className="text-xs text-muted-foreground pl-2"> ({standings.paRank})</span>
              </TableCell>
              <TableCell>
                <span className="text-lg font-extrabold">
                  {standings.member.wins} &nbsp; - &nbsp;
                  {standings.member.losses}
                  {standings.member.ties > 0 ? ` &nbsp; - &nbsp;${standings.member.ties}` : ''}
                </span>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
