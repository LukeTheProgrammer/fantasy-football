import { type League, type LeagueMember, type LeagueMemberRoster, type NflGame } from '@/types/models';
import { c } from '@/lib/conv';
import MemberTabHeader from '@/components/leagues/tab-content/member-tab-header';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface RostersTabProps {
  league: League;
  selectedMember: LeagueMember | null;
  selectedWeek: string;
  nfl_games: NflGame[];
};

interface RostersTabPlayer {
  id: string;
  name: string;
  position: string;
  team: string;
  headshot: string | null;
  overall_rank: number;
  position_rank: number;
  espn_projection: string;
  espn_diff: string;
  fantasy_points: string;
  game: NflGame | undefined;
};

export default function ShowLeague({ league, selectedMember, selectedWeek, nfl_games }: RostersTabProps) {

  const gameDate = (game: NflGame) => {
    const date = new Date(game.start_time);
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const day = dayNames[date.getDay()];
    // Format to two-digit 12-hour time without AM/PM
    const hours24 = date.getHours();
    const hours12 = hours24 % 12 === 0 ? 12 : hours24 % 12;
    const hoursStr = hours12.toString().padStart(2, '0');
    const minutesStr = date.getMinutes().toString().padStart(2, '0');
    const time = `${hoursStr}:${minutesStr}`;
    return (
      <>
        <span className="pr-1">{day}</span>
        <span>{time}</span>
      </>
    );
  };

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

  const getPlayers = (rosters: LeagueMemberRoster[]): RostersTabPlayer[] => {
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

    const weekNum = Number(selectedWeek.replace('Week ', ''));

    rosters
      .filter(roster => roster.week === weekNum)
      .forEach(roster => {
        const abb = roster.player.position.abbreviation;
        const tid = roster.player.team_id;
        const game = roster.nfl_game ?? nfl_games.find(
          g => g.week === weekNum && (g.away_team_id === tid || g.home_team_id === tid)
        );

        if (abb in positions) {
          const points = c(roster.fantasy_points).toFloat();
          const espn = c(roster.espn_projected_points).toFloat();
          const espnDiff = (points > 0 && espn > 0) ? espn - points : 0;

          const player: RostersTabPlayer = {
            id:              roster.player.id.toString(),
            name:            roster.player.full_name,
            position:        roster.player.position.abbreviation,
            team:            roster.player.team.abbreviation,
            game:            game,
            headshot:        roster.player.headshot,
            overall_rank:    c(roster.overall_rank).toNumber(),
            position_rank:   c(roster.position_rank).toNumber(),
            fantasy_points:  points > 0 ? c(points).toString() : '--',
            espn_projection: espn > 0 ? c(espn).toString() : '--',
            espn_diff:       espnDiff != 0 ? espnDiff.toFixed(2) : '',
          };

          positions[abb].push(player);
        }
      });

    const players: RostersTabPlayer[] = [];

    for (const p in positions) {
      const pos = positions[p].sort((a: RostersTabPlayer, b: RostersTabPlayer) => {
        const aVal = a.overall_rank > 0 ? a.overall_rank : 9999;
        const bVal = b.overall_rank > 0 ? b.overall_rank : 9999;
        return aVal - bVal;
      });
      pos.forEach((player: RostersTabPlayer) => players.push(player));
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
            <TableHead className='text-center'>ESPN Proj</TableHead>
            <TableHead className='text-center'>Points</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {getPlayers(selectedMember?.rosters || []).map((player) => (
            <TableRow key={player.id}>
              <TableCell className="text-center border-s-4" style={{ borderLeftColor: posBorderColor(player.position) }}>
                {player.position}
              </TableCell>
              <TableCell className="flex items-center justify-start">
                <div className="w-[4em] flex items-center justify-center">
                  {player.headshot && (
                    <img src={player.headshot} alt={player.name} className="h-10" />
                  )}
                </div>
                <div className="pl-2">
                  <p className="font-bold">{player.name}</p>
                  <p className="text-xs text-muted-foreground">
                    {player.team} &nbsp; • &nbsp; {player.position} {player.position_rank}
                  </p>
                </div>
              </TableCell>
              <TableCell className="text-center text-xs">
                <p className="font-extrabold text-lg">
                  {! player.game ? ('Bye') : (
                    (player.team === player.game.away_team.abbreviation
                      ? <span>@ {player.game.home_team.abbreviation}</span>
                      : <span> {player.game.away_team.abbreviation}</span>
                    )
                  )}
                </p>
                <p className="pl-2 text-xs text-muted-foreground">
                  {player.game && gameDate(player.game)}
                </p>
              </TableCell>
              <TableCell className="text-center">
                <p className="font-extrabold text-lg">
                  {player.espn_projection}
                </p>
                <p className="pl-2 text-xs text-muted-foreground">
                  {player.espn_diff}
                </p>
              </TableCell>
              <TableCell className="text-center">
                <p className="font-extrabold text-lg">
                  {player.fantasy_points}
                </p>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
