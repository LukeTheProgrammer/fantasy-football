import TeamAvatar from '@/components/leagues/team-avatar';
import { c } from '@/lib/conv';
import { rankName } from '@/lib/utils';
import { useMemo } from 'react';
import { type LeagueResource, type LeagueMemberResource } from '@/types/resources';

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
    const pfRanks: { member: LeagueMemberResource; pf: number }[] = [];
    const paRanks: { member: LeagueMemberResource; pa: number }[] = [];

    const standings: Standing[] = [];

    league.members
      .sort((a: LeagueMemberResource, b: LeagueMemberResource) => b.wins - a.wins)
      .forEach((m: LeagueMemberResource) => {
        const pf = c(m.points_for).toFloat();
        const pa = c(m.points_against).toFloat();

        pfRanks.push({member: m, pf: pf});
        paRanks.push({member: m, pa: pa});

        const standing: Standing = {
          memberId: m.id,
          member: m,
          pointsFor: pf,
          pointsAgainst: pa,
          pfRank: '',
          paRank: '',
          record: `${m.wins} - ${m.losses} - ${m.ties}`,
        };

        standings.push(standing);
      });

      pfRanks.sort((a, b) => b.pf - a.pf).forEach((r, k) => {
        const si = standings.findIndex(s => s.memberId === r.member.id);

        if (si > -1) {
          standings[si].pfRank = rankName(k + 1);
        }
      });

      paRanks.sort((a, b) => a.pa - b.pa).forEach((r, k) => {
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
            <div className="min-w-[8em] pr-6">
              <p className="text-xs text-muted-foreground">Points For</p>
              <p>
                <span className="text-lg font-extrabold">{standings.pointsFor.toFixed(2)}</span>
                <span className="text-xs text-muted-foreground pl-2"> ({standings.pfRank})</span>
              </p>
            </div>
            <div className="min-w-[8em] pr-6">
              <p className="text-xs text-muted-foreground">Points Against</p>
              <p>
                <span className="text-lg font-extrabold">{standings.pointsAgainst.toFixed(2)}</span>
                <span className="text-xs text-muted-foreground pl-2"> ({standings.paRank})</span>
              </p>
            </div>
            <div className="min-w-[8em]">
              <p className="text-xs text-muted-foreground">Record</p>
              <p className="text-lg font-extrabold">
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
