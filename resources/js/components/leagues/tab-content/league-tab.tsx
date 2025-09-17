import MemberBadge from '@/components/leagues/member-badge';
import { Player, type League, type LeagueMember, type LeagueMemberRoster, type NflGame, type LeagueMatchup } from '@/types/models';
import { useState, useEffect } from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

interface LeagueTabProps {
  league: League;
  nfl_games: NflGame[];
};

interface LeageTabMatchup extends LeagueMatchup {
  user_is_home: boolean;
  complete: boolean;
  home_score_int: number;
  away_score_int: number;
  projected_home_score_int: number;
  projected_away_score_int: number;
}

export default function ShowLeague({ league, nfl_games }: LeagueTabProps) {
  const [selectedMemberId, setSelectedMemberId] = useState<int | string | null>(null);
  const [selectedMember, setSelectedMember] = useState<LeagueMember | null>(null);
  const [selectedWeek, setSelectedWeek] = useState<string>('Week 1');

  const fantasyPointsWeeks = league.fantasy_points_weeks || [];

  // Select the first member by default when the component mounts
  useEffect(() => {
    if (league.members.length > 0 && !selectedMemberId) {
      setSelectedMemberId(league.members[0].id.toString());
    }
  }, [league.members, selectedMemberId]);

  useEffect(() => {
    if (league.members.length > 0 && selectedMemberId) {
      const lm = league.members.find(m => m.id.toString() === selectedMemberId) || league.members[0];
      setSelectedMember(lm);
    }
  }, [league.members, selectedMemberId]);

  useEffect(() => {
    if (!selectedWeek) {
      setSelectedWeek('Week 1');
    }
  }, [selectedWeek]);

  const handleMemberIdChange = (memberId: string) => {
    setSelectedMemberId(memberId);
  };

  const handleWeekChange = (week: string) => {
    setSelectedWeek(week);
  };

  const gameDate = (game: NflGame) => {
    const date = new Date(game.start_time);
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const day = dayNames[date.getDay()];
    const time = date.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    });
    return (
      <>
        <span className="pr-2">{day}</span>
        <span className="pr-2">{time}</span>
      </>
    );
  };

  const matchupWeeks = Array(16).fill('').map((_, i) => `Week ${i + 1}`);

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
      const tid = roster.player.team_id;
      const weekNum = Number(selectedWeek.replace('Week ', ''));
      const game = nfl_games.find(g => g.week === weekNum && (g.away_team_id === tid || g.home_team_id === tid));

      if (game && abb in positions) {
        const fps = fantasyPointsWeeks.find(fpw => fpw.nfl_game_id === game.id);

        const proj = new Number(fps?.espn_projected_points || 0);
        const actual = new Number(fps?.points || 0);

        positions[abb].push({
          ...roster.player,
          projected_points: typeof proj === 'number' && proj > 0 ? proj.toFixed(1) : '--',
          actual_points: typeof actual === 'number' && actual > 0 ? actual.toFixed(1) : '--',
          game: game,
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

  function getMatchups(member: LeagueMember): LeageTabMatchup[] {
    return league.matchups.filter(m => m.home_member_id === member.id || m.away_member_id === member.id)
      .sort((a, b) => a.week - b.week)
      .map(m => {
        return {
        ...m,
        home_score_int: m.home_score !== null ? Math.round(m.home_score) : 0,
        away_score_int: m.away_score !== null ? Math.round(m.away_score) : 0,
        projected_home_score_int: m.home_projected_score !== null ? Math.round(m.home_projected_score) : 0,
        projected_away_score_int: m.away_projected_score !== null ? Math.round(m.away_projected_score) : 0,
        user_is_home: m.home_member_id === member.id,
        complete: (
          m.home_score !== null &&
          m.home_score > 0 &&
          m.away_score !== null &&
          m.away_score > 0
        ),
      };
    });
  }

  return (
    <div className="grid grid-cols-3 gap-6">
      <div className="col-span-2 p-4 mb-8 rounded-lg border bg-card">
        <div className="mb-4 flex items-center justify-between">
          <h4 className="text-lg font-semibold">{selectedMember?.team_name}'s Roster</h4>
          <div className="flex items-center space-x-2">
            <Select value={selectedMemberId} onValueChange={(value) => handleMemberIdChange(value)}>
              <SelectTrigger className="w-[16em]">
                <SelectValue placeholder={`Week ${selectedWeek}`} />
              </SelectTrigger>
              <SelectContent>
                {league.members.map((member) => (
                  <SelectItem key={member.id} value={member.id.toString()}>
                    {member.team_name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={selectedWeek} onValueChange={(value) => handleWeekChange(value)}>
              <SelectTrigger className="w-[16em]">
                <SelectValue placeholder={`Week ${selectedWeek}`} />
              </SelectTrigger>
              <SelectContent>
                {matchupWeeks.map((week) => (
                  <SelectItem key={week} value={week}>
                    {week}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>

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
                <TableCell className="text-center text-xs">
                  <p>{player.game && (
                    (player.team_id === player.game.away_team_id
                      ? <span className="pr-2">@ {player.game.home_team.abbreviation}</span>
                      : <span className="pr-2"> {player.game.away_team.abbreviation}</span>
                    )
                  )}</p>
                  <p>{player.game && gameDate(player.game)}</p>
                </TableCell>
                <TableCell className="text-center">
                  {player?.projected_points}
                </TableCell>
                <TableCell className="text-center">
                  {player?.actual_points}
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <div className="col-span-1 p-4 mb-8 rounded-lg border bg-card">
        <h4 className="text-lg font-semibold mb-4">Matchups</h4>
        {selectedMember && getMatchups(selectedMember).map((matchup, k) => (
          <div key={k} className="flex items-center justify-between rounded-md border p-3 mb-2">
            <div>{matchup.week}</div>
            <p className="grow-1 text-xs pl-2">
              {matchup.user_is_home
                ? (matchup.away_team.team_name)
                : (matchup.home_team.team_name)
              }
            </p>
            <div className="flex items-center justify-center">
              {!matchup.complete ? ('') : (
                <>
                  <div className="min-w-10 text-left">{matchup.user_is_home ? matchup.home_score_int : matchup.away_score_int}</div>
                  <div className="min-w-2 text-center">-</div>
                  <div className="min-w-10 text-right">{matchup.user_is_home ? matchup.away_score_int : matchup.home_score_int}</div>
                </>
              )}
            </div>
            <div className="pl-2 min-w-10 text-center">
              {!matchup.complete ? ('') : (
                <>
                {matchup.user_is_home && matchup.complete
                  ? (matchup.home_score_int > matchup.away_score_int ? 'W' : 'L')
                  : (matchup.away_score_int > matchup.home_score_int ? 'W' : 'L')
                }
                </>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
