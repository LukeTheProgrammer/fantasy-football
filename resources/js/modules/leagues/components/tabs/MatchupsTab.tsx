import { c } from '@/common/helpers/conv';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { MemberTabHeader } from '@/modules/leagues/components/tabs/MemberTabHeader';
import { TeamAvatar } from '@/modules/leagues/components/TeamAvatar';
import { type LeagueMatchupResource, type LeagueMemberResource, type LeagueResource, type LeagueTeamResource } from '@/types/resources';
import { useMemo } from 'react';

interface MatchupsTabProps {
  league: LeagueResource;
  selectedMember: LeagueMemberResource | null;
}

interface Matchup {
  week: number;
  teamA: {
    team: LeagueTeamResource;
    points: number;
    proj_points: number;
  };
  teamB: {
    team: LeagueTeamResource;
    points: number;
    proj_points: number;
  };
  complete: boolean;
}

export function MatchupsTab({ league, selectedMember }: MatchupsTabProps) {
  const { matchups } = useMemo((): { matchups: Matchup[] } => {
    if (!selectedMember) {
      console.error('No selected member');
      return { matchups: [] as Matchup[] };
    }

    const leagueMatchups: LeagueMatchupResource[] = [];
    const matchups: Matchup[] = [];

    Object.entries(league.matchups).forEach(([, matchups]) => {
      matchups.forEach((m: LeagueMatchupResource) => {
        const mid = selectedMember.id;
        const hid = m.home_team.id;
        const aid = m.away_team?.id;

        // A playoff bye has no opponent, so there is no head to head to show.
        if (m.away_team && (hid === mid || aid === mid)) {
          leagueMatchups.push(m);
        }
      });
    });

    leagueMatchups.sort((a, b) => a.week - b.week);

    leagueMatchups.map((m) => {
      const homeTeam = m.home_team.id === selectedMember.id ? 'teamA' : 'teamB';

      const opponent = m.away_team as LeagueTeamResource;

      const teamA = homeTeam === 'teamA' ? m.home_team : opponent;
      const teamB = homeTeam === 'teamB' ? m.home_team : opponent;

      const teamAPoints = homeTeam === 'teamA' ? m.home_score : m.away_score;
      const teamBPoints = homeTeam === 'teamB' ? m.home_score : m.away_score;

      const teamAProjPoints = homeTeam === 'teamA' ? m.home_projected_score : m.away_projected_score;
      const teamBProjPoints = homeTeam === 'teamB' ? m.home_projected_score : m.away_projected_score;

      const matchup: Matchup = {
        week: m.week,
        teamA: {
          team: teamA,
          points: c(teamAPoints).toFloat(),
          proj_points: c(teamAProjPoints).toFloat(),
        },
        teamB: {
          team: teamB,
          points: c(teamBPoints).toFloat(),
          proj_points: c(teamBProjPoints).toFloat(),
        },
        complete: c(m.home_score).toInt() > 0 && c(m.away_score).toInt() > 0,
      };

      matchups.push(matchup);
    });

    return { matchups };
  }, [league.matchups, selectedMember]);

  if (selectedMember === null) {
    return (
      <div>
        <h1>No team selected</h1>
      </div>
    );
  }

  return (
    <div className="col-span-1 mb-8 rounded-lg border bg-card p-4">
      <MemberTabHeader league={league} selectedMember={selectedMember} />

      <div className="mt-4">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Week</TableHead>
              <TableHead>Team</TableHead>
              <TableHead>{selectedMember.team_name}</TableHead>
              <TableHead>Opponent</TableHead>
              <TableHead>&nbsp;</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {matchups.map((matchup, k) => (
              <TableRow key={k}>
                <TableCell>Week {matchup.week}</TableCell>
                <TableCell>
                  <div className="flex items-center justify-start space-x-6">
                    <TeamAvatar member={matchup.teamB.team} />
                    <div>
                      <p>
                        <span className="font-bold">{matchup.teamB.team.team_name}</span>
                        <span className="pl-2 text-muted-foreground">{matchup.teamB.team.owner_name}</span>
                      </p>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="font-bold">{matchup.complete && matchup.teamA.points}</TableCell>
                <TableCell className="font-bold">{matchup.complete && matchup.teamB.points}</TableCell>
                <TableCell className="min-w-10 pl-2 text-center font-bold">
                  {!matchup.complete ? '--' : <>{matchup.teamA.points > matchup.teamB.points ? 'W' : 'L'}</>}
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  );
}
