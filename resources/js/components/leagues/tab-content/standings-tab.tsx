import { type League, type LeagueMember } from '@/types/models';
import TeamAvatar from '@/components/leagues/team-avatar';
import { c } from '@/lib/conv';
import { useMemo } from 'react';

interface StandingsTabProps {
  league: League;
}

export default function StandingsTab({ league }: StandingsTabProps) {

  const rankName = (rank: number) => {
    if (rank === 1) {
      return '1st';
    } else if (rank === 2) {
      return '2nd';
    } else if (rank === 3) {
      return '3rd';
    } else {
      return `${rank}th`;
    }
  };

  const { standings } = useMemo(() => {
    const pfRanks: { member: LeagueMember; pf: number }[] = [];
    const paRanks: { member: LeagueMember; pa: number }[] = [];

    const standings = league.members
      .sort((a, b) => b.wins - a.wins)
      .map(m => {
        const pf = c(m.points_for).toFloat();
        const pa = c(m.points_against).toFloat();

        pfRanks.push({member: m, pf: pf});
        paRanks.push({member: m, pa: pa});

        return {
          memberId: m.id,
          member: m,
          pointsFor: pf,
          pointsAgainst: pa,
          pfRank: '',
          paRank: '',
          record: `${m.wins} - ${m.losses} - ${m.ties}`,
        };
      });

      pfRanks.sort((a, b) => b.pf - a.pf).map((r, k) => {
        const si = standings.findIndex(s => s.memberId === r.member.id);

        if (si > -1) {
          standings[si].pfRank = rankName(k + 1);
        }
      });

      paRanks.sort((a, b) => a.pa - b.pa).map((r, k) => {
        const si = standings.findIndex(s => s.memberId === r.member.id);

        if (si > -1) {
          standings[si].paRank = rankName(k + 1);
        }
      });

    return { standings };
  }, [league.members]);

  return (
    <div className="w-full p-4 mb-8 rounded-lg border bg-card">

      <div className="w-full flex items-center justify-start mb-6">
        <h4 className="text-lg font-semibold">Standings</h4>
      </div>

      {standings.map((standings, k) => (
        <div key={k} className="flex items-center justify-between rounded-md border p-3 mb-2">
          <div className="flex items-center justify-start space-x-4">
            <TeamAvatar member={standings.member} />
            <div>
              <h4 className="text-lg font-semibold">{standings.member.team_name}</h4>
              <p className="text-xs text-muted-foreground">{standings.member.owner_name}</p>
            </div>
          </div>
          <div className="flex align-center justify-end space-x-8">
            <div className="pr-6">
              <p className="text-xs text-muted-foreground">Points For</p>
              <p>
                {standings.pointsFor.toFixed(2)}
                <span className="text-xs text-muted-foreground"> ({standings.pfRank})</span>
              </p>
            </div>
            <div className="pr-6">
              <p className="text-xs text-muted-foreground">Points Against</p>
              <p>
                {standings.pointsAgainst.toFixed(2)}
                <span className="text-xs text-muted-foreground"> ({standings.paRank})</span>
              </p>
            </div>
            <div className="">
              <p className="text-xs text-muted-foreground">Record</p>
              <p>
                {standings.member.wins} &nbsp; - &nbsp;
                {standings.member.losses}
                <>{standings.member.ties > 0 ? ` &nbsp; - &nbsp;${standings.member.ties}` : ''}</>
              </p>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
