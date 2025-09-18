import TeamAvatar from '@/components/leagues/team-avatar';
import { c } from '@/lib/conv';
import { type League, type LeagueMember } from '@/types/models';
import { useMemo } from 'react';

interface MemberTabHeaderProps {
  league: League;
  selectedMember: LeagueMember | null;
};

export default function MemberTabHeader({ league, selectedMember }: MemberTabHeaderProps) {
  const { pointsFor, pointsAgainst } = useMemo(() => {
    if (!selectedMember) {
      return { pointsFor: 0, pointsAgainst: 0 };
    }

    let pf = 0;
    let pa = 0;

    league.matchups
      .filter(m => m.home_member_id === selectedMember.id || m.away_member_id === selectedMember.id)
      .map(m => {
        const homeTeam = m.home_member_id === selectedMember.id ? 'teamA' : 'teamB';

        const teamAPoints = homeTeam === 'teamA' ? m.home_score : m.away_score;
        const teamBPoints = homeTeam === 'teamB' ? m.home_score : m.away_score;

        pf += c(teamAPoints).toFloat();
        pa += c(teamBPoints).toFloat();
      });

    return { pointsFor: pf, pointsAgainst: pa };
  }, [league.matchups, selectedMember]);

  if (selectedMember === null) {
    return (
      <div></div>
    );
  }

  return (
    <div className="w-full flex items-center justify-between mb-6">
      <div className="flex items-center justify-start space-x-2">
        <TeamAvatar member={selectedMember || league.members[0]} />
        <h4 className="text-lg font-semibold">{selectedMember?.team_name}</h4>
      </div>
      <div className="flex align-center justify-end space-x-2">
        <div className="pr-6">
          <p className="text-xs text-muted-foreground">Points For</p>
          <p>{pointsFor.toFixed(2)}</p>
        </div>
        <div className="pr-6">
          <p className="text-xs text-muted-foreground">Points Against</p>
          <p>{pointsAgainst.toFixed(2)}</p>
        </div>
        <div className="">
          <p className="text-xs text-muted-foreground">Record</p>
          <p>
            {selectedMember.wins} &nbsp; - &nbsp;
            {selectedMember.losses}
            <>{selectedMember.ties > 0 ? ` &nbsp; - &nbsp;${selectedMember.ties}` : ''}</>
          </p>
        </div>
      </div>
    </div>
  );
}
