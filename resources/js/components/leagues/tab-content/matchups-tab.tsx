import MemberTabHeader from '@/components/leagues/tab-content/member-tab-header';
import TeamAvatar from '@/components/leagues/team-avatar';
import { c } from '@/lib/conv';
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

export default function MatchupsTab({ league, selectedMember }: MatchupsTabProps) {
  const { matchups } = useMemo((): { matchups: Matchup[] } => {
    if (!selectedMember) {
      return { matchups: [] as Matchup[] };
    }

    const leagueMatchups: LeagueMatchupResource[] = [];
    const matchups: Matchup[] = [];

    Object.entries(league.matchups).forEach(([, matchups]) => {
      matchups.forEach((m: LeagueMatchupResource) => {
        if (m.home_team.id === selectedMember.id || m.away_team.id === selectedMember.id) {
          leagueMatchups.push(m);
        }
      });
    });

    leagueMatchups.sort((a, b) => a.week - b.week);

    leagueMatchups.map((m) => {
      const homeTeam = m.home_team.id === selectedMember.id ? 'teamA' : 'teamB';

      const teamA = homeTeam === 'teamA' ? m.home_team : m.away_team;
      const teamB = homeTeam === 'teamB' ? m.home_team : m.away_team;

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

      {matchups.map((matchup, k) => (
        <div key={k} className="mb-2 flex items-center justify-between rounded-md border p-3">
          <div className="text-muted-foreground">Week {matchup.week}</div>
          <div className="grow-1 px-4 text-xs">
            <div className="flex items-center justify-start space-x-6">
              <TeamAvatar member={matchup.teamB.team} />
              <div>
                <p className="text-lg">{matchup.teamB.team.team_name}</p>
                <p className="text-muted-foreground">{matchup.teamB.team.owner_name}</p>
              </div>
            </div>
          </div>
          <div className="flex items-center justify-center">
            {!matchup.complete ? (
              ''
            ) : (
              <>
                <div className="min-w-10 text-left">{matchup.teamA.points}</div>
                <div className="min-w-2 text-center">-</div>
                <div className="min-w-10 text-right">{matchup.teamB.points}</div>
              </>
            )}
          </div>
          <div className="min-w-10 pl-2 text-center">{!matchup.complete ? '--' : <>{matchup.teamA.points > matchup.teamB.points ? 'W' : 'L'}</>}</div>
        </div>
      ))}
    </div>
  );
}
