import MemberTabHeader from '@/components/leagues/tab-content/member-tab-header';
import { type LeagueResource, type LeagueMemberResource, type LeagueRosterResource } from '@/types/resources';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';


interface RostersTabProps {
  league: LeagueResource;
  selectedMember: LeagueMemberResource | null;
  selectedWeek: string;
};

export default function ShowLeague({ league, selectedMember, selectedWeek }: RostersTabProps) {

  const posBorderColor = (pos: string) => {
    switch (pos) {
      case 'QB':
        return '#75A374';
      case 'RB':
        return '#5882FA';
      case 'WR':
        return '#F5CA49';
      case 'TE':
        return '#DE926D';
      default:
        return '#999999';
    }
  };

  // const getPlayers = (rosters: []): [] => rosters;

  const getPlayers = (memberRosters: Record<string, LeagueRosterResource[]>): LeagueRosterResource[] => {
    if (!memberRosters || Object.keys(memberRosters).length === 0) {
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

    const weekNum = Number(selectedWeek.replace('Week ', ''));
    const weekRoster = memberRosters[weekNum] || {};

    weekRoster.forEach(memberRoster => {
      const pos = memberRoster.player.position;

      if (pos in positions) {
        positions[pos].push(memberRoster);
      }
    });

    const players: LeagueRosterResource[] = [];

    for (const p in positions) {
      const pos = positions[p].sort((a: LeagueRosterResource, b: LeagueRosterResource) => {
        const aVal = a.overall_rank > 0 ? a.overall_rank : 9999;
        const bVal = b.overall_rank > 0 ? b.overall_rank : 9999;
        return aVal - bVal;
      });
      pos.forEach((player: LeagueRosterResource) => players.push(player));
    }

    return players;
  }

  if (selectedMember === null) {
    return (
      <div>
        <h1>No team selected</h1>
      </div>
    );
  }

  return (
    <div className="w-full p-4 mb-8 rounded-lg border bg-card">
      <MemberTabHeader league={league} selectedMember={selectedMember} />

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead className='text-center'>POS</TableHead>
            <TableHead>Player</TableHead>
            <TableHead className='text-center'>Game</TableHead>
            <TableHead className='text-center'>FP Pos</TableHead>
            <TableHead className='text-center'>FP Points</TableHead>
            <TableHead className='text-center'>ESPN Points</TableHead>
            <TableHead className='text-center'>Points</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {getPlayers(selectedMember?.rosters || {}).map((roster) => (
            <TableRow key={roster?.player?.id}>
              <TableCell className="text-center border-s-4" style={{ borderLeftColor: posBorderColor(roster.player.position) }}>
                {roster.player.position}
              </TableCell>
              <TableCell className="flex items-center justify-start">
                <div className="w-[4em] flex items-center justify-center">
                  {roster.player.headshot && (
                    <img src={roster.player.headshot} alt={roster.player.full_name} className="h-10" />
                  )}
                </div>
                <div className="pl-2">
                  <p className="font-bold">{roster?.player?.full_name}</p>
                  <p className="text-xs text-muted-foreground">
                    {roster.player.team} &nbsp; • &nbsp; {roster.player.position} {roster.position_rank}
                  </p>
                </div>
              </TableCell>
              <TableCell className="text-center text-xs">
                <p className="font-extrabold text-lg">
                  {! roster.nfl_game ? ('Bye') : (
                    (roster.player.team === roster.nfl_game.away_team?.id
                      ? <span>@ {roster.nfl_game.home_team?.id}</span>
                      : <span> {roster.nfl_game.away_team?.id}</span>
                    )
                  )}
                </p>
                <p className="pl-2 text-xs text-muted-foreground">
                  {roster.nfl_game.day} {roster.nfl_game.time}
                </p>
              </TableCell>
              <TableCell className="text-center">
                <p className="font-extrabold text-lg">
                  {roster.player_projection.fp_pos_rank}
                </p>
              </TableCell>
              <TableCell className="text-center">
                <p className="font-extrabold text-lg">
                  {roster.player_projection.fp_points}
                </p>
                <p className="pl-2 text-xs text-muted-foreground">
                  {roster.fp_diff}
                </p>
              </TableCell>
              <TableCell className="text-center">
                <p className="font-extrabold text-lg">
                  {roster.player_projection.espn_points}
                </p>
                <p className="pl-2 text-xs text-muted-foreground">
                  {roster.espn_diff}
                </p>
              </TableCell>
              <TableCell className="text-center">
                <p className="font-extrabold text-lg">
                  {roster.fantasy_points}
                </p>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
