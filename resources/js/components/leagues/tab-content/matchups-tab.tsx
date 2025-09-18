import { type League, type LeagueMember } from '@/types/models';
import TeamAvatar from '@/components/leagues/team-avatar';
import MemberTabHeader from '@/components/leagues/tab-content/member-tab-header';
import { c } from '@/lib/conv';
import { useMemo } from 'react';

interface MatchupsTabbProps {
  league: League;
  selectedMember: LeagueMember | null;
}

interface MatchupsTabMatchup {
  week: number;
  complete: boolean;
  // Team belonging to selectedMember
  teamA: MatchupsTabTeam;
  // Team belonging to opponent
  teamB: MatchupsTabTeam;
};

interface MatchupsTabTeam {
  team: LeagueMember;
  points: number;
  proj_points: number;
}

export default function MatchupsTabb({ league, selectedMember }: MatchupsTabbProps) {
  const { matchups } = useMemo(() => {
    if (!selectedMember) {
      return { matchups: [] as MatchupsTabMatchup[] };
    }

    const matchups: MatchupsTabMatchup[] = league.matchups
      .filter(m => m.home_member_id === selectedMember.id || m.away_member_id === selectedMember.id)
      .sort((a, b) => a.week - b.week)
      .map(m => {
        const homeTeam = m.home_member_id === selectedMember.id ? 'teamA' : 'teamB';

        const teamA = homeTeam === 'teamA' ? m.home_team : m.away_team;
        const teamB = homeTeam === 'teamB' ? m.home_team : m.away_team;

        const teamAPoints = homeTeam === 'teamA' ? m.home_score : m.away_score;
        const teamBPoints = homeTeam === 'teamB' ? m.home_score : m.away_score;

        const teamAProjPoints = homeTeam === 'teamA' ? m.home_projected_score : m.away_projected_score;
        const teamBProjPoints = homeTeam === 'teamB' ? m.home_projected_score : m.away_projected_score;

        return {
          week: m.week,
          teamA:  {
            team: teamA,
            points: c(teamAPoints).toFloat(),
            proj_points: c(teamAProjPoints).toFloat(),
          },
          teamB: {
            team: teamB,
            points: c(teamBPoints).toFloat(),
            proj_points: c(teamBProjPoints).toFloat(),
          },
          complete: (
            c(m.home_score).toInt() > 0 &&
            c(m.away_score).toInt() > 0
          ),
        };
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
    <div className="col-span-1 p-4 mb-8 rounded-lg border bg-card">

      <MemberTabHeader league={league} selectedMember={selectedMember} />

      {matchups.map((matchup, k) => (
        <div key={k} className="flex items-center justify-between rounded-md border p-3 mb-2">
          <div className="text-muted-foreground">Week {matchup.week}</div>
          <div className="grow-1 text-xs px-4">
            <div className="flex items-center justify-start space-x-6">
              <TeamAvatar member={matchup.teamB.team} />
              <div>
                <p className="text-lg">{matchup.teamB.team.team_name}</p>
                <p className="text-muted-foreground">{matchup.teamB.team.owner_name}</p>
              </div>
            </div>
          </div>
          <div className="flex items-center justify-center">
            {!matchup.complete ? ('') : (
              <>
                <div className="min-w-10 text-left">{matchup.teamA.points}</div>
                <div className="min-w-2 text-center">-</div>
                <div className="min-w-10 text-right">{matchup.teamB.points}</div>
              </>
            )}
          </div>
          <div className="pl-2 min-w-10 text-center">
            {!matchup.complete ? ('--') : (
              <>
              {matchup.teamA.points > matchup.teamB.points ? 'W' : 'L'}
              </>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
