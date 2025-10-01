import TeamAvatar from '@/components/leagues/team-avatar';
import { useMemo } from 'react';
import { type LeagueResource, type LeagueMemberResource } from '@/types/resources';
import { rankName } from '@/lib/utils';

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

      {standings.map((standings, k) => (
        <div key={k} className="flex items-center justify-between rounded-md border p-3 mb-2">
          <div className="flex items-center justify-start space-x-4">
            <div>
              {rankName(k + 1)}
            </div>
            <TeamAvatar member={standings.member} />
            <div>
              <h4 className="text-lg font-semibold">{standings.member.team_name}</h4>
              <p className="text-xs text-muted-foreground">{standings.member.owner_name}</p>
            </div>
          </div>
          <div className="flex align-center justify-end space-x-8">
            <div className="min-w-[8em] pr-4">
              <p className="text-xs text-muted-foreground">Points For</p>
              <p>
                <span className="text-lg font-extrabold">{standings.pointsFor}</span>
                <span className="text-xs text-muted-foreground pl-2"> ({standings.pfRank})</span>
              </p>
            </div>
            <div className="min-w-[8em] pr-4">
              <p className="text-xs text-muted-foreground">Points Against</p>
              <p>
                <span className="text-lg font-extrabold">{standings.pointsAgainst}</span>
                <span className="text-xs text-muted-foreground pl-2"> ({standings.paRank})</span>
              </p>
            </div>
            <div className="min-w-[4em] text-right">
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
