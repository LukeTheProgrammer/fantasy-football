import MemberBadge from '@/components/leagues/member-badge';
import { Player, type League, type LeagueMember, type LeagueMemberRoster, type FantasyPointsWeek } from '@/types/models';
import { useState, useEffect } from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

interface LeagueTabProps {
  league: League;
}

export default function ShowLeague({ league }: LeagueTabProps) {
  // const { auth } = usePage<SharedData>().props;
  // const userId = auth.user.id;
  // const userIsAdmin = isUserLeagueAdmin(league, userId);

  const [selectedMember, setSelectedMember] = useState<LeagueMember | null>(null);

  const fantasyPointsWeeks = league.fantasy_points_weeks || [];

  // Select the first member by default when the component mounts
  useEffect(() => {
    if (league.members.length > 0 && !selectedMember) {
      setSelectedMember(league.members[0]);
    }
  }, [league.members, selectedMember]);

  const handleMemberClick = (member: LeagueMember) => {
    setSelectedMember(prevMember => prevMember?.id === member.id ? null : member);
  };

  const matchupWeeks = Array(16).fill(0);

  function getPlayers(rosters: LeagueMemberRoster[]): Player[] {
    if (!rosters || rosters.length === 0) {
      return [];
    }

    const positions = {
      QB: [],
      RB: [],
      WR: [],
      TE: [],
      DST: [],
      K: [],
    };

    rosters.forEach(roster => {
      const abb = roster.player.position.abbreviation;

      if (abb in positions) {
        const proj = fantasyPointsWeeks.find(fpw => fpw.player_id === roster.player.id);
        const ptVal = new Number(proj?.espn_projected_points || 0);
        const pts = ptVal.toFixed(1) || 0;

        positions[abb].push({
          ...roster.player,
          projected_points: pts || 0,
        });
      }
    });

    return [
      ...positions.QB,
      ...positions.RB,
      ...positions.WR,
      ...positions.TE,
      ...positions.DST,
      ...positions.K,
    ];
  }

  return (
    <div className="grid grid-cols-4 gap-6">

      <div className="col-span-1 p-4 mb-8 rounded-lg border bg-card">
          {league.members.map((member) => (
            <div key={member.id} className="mb-2">
              <MemberBadge
                member={member}
                onClick={() => handleMemberClick(member)}
                isSelected={selectedMember?.id === member.id}
              />
            </div>
          ))}
      </div>

      <div className="col-span-2 p-4 mb-8 rounded-lg border bg-card">
        {selectedMember !== null && (
          <>
            <h4 className="text-lg font-semibold mb-4">{selectedMember.team_name}'s Roster</h4>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className='text-center'>POS</TableHead>
                  <TableHead>Player</TableHead>
                  <TableHead>Proj.</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {getPlayers(selectedMember?.rosters || []).map((player) => (
                  <TableRow key={player.id}>
                    <TableCell className="text-center">
                      {player.position.abbreviation}
                    </TableCell>
                    <TableCell className="pl-0 flex items-center justify-start">
                      <div className="w-[4em]">
                        {player.headshot && (
                          <img src={player.headshot} alt={player.full_name} className="h-10" />
                        )}
                      </div>
                      <div className="pl-2">
                        <p>{player.full_name}</p>
                        <p className="text-xs text-muted-foreground">{player.team.abbreviation}</p>
                      </div>
                    </TableCell>
                    <TableCell className="text-center">
                      {player?.projected_points}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </>
        )}
      </div>

      <div className="col-span-1 p-4 mb-8 rounded-lg border bg-card">
        <h4 className="text-lg font-semibold mb-4">Matchup Schedule</h4>
        {matchupWeeks.map((v, k) => (
          <div key={k} className="flex items-center justify-between rounded-md border p-3 mb-2">
            Week {k + 1}
          </div>
        ))}
      </div>
    </div>
  );
}
