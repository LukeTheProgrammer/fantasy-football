import { type LeagueRosterResource } from '@/types/resources';
import { ShowPoints } from '@/modules/scoring/components/ShowPoints';
import { ShowRank } from '@/modules/scoring/components/ShowRank';
import {
  TableCell,
  TableRow,
} from '@/components/ui/table';

interface RostersTableRowProps {
  roster: LeagueRosterResource;
};

export function RostersTableRow({ roster }: RostersTableRowProps) {

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

  return (
    <TableRow key={roster?.player?.id}>
      <TableCell className="py-1 text-center border-s-4" style={{ borderLeftColor: posBorderColor(roster.player.position) }}>
        {roster.lineup_slot_name ? roster.lineup_slot_name : roster.player.position}
      </TableCell>
      <TableCell className="py-1 flex items-center justify-start">
        <div className="w-[4em] flex items-center justify-center">
          {roster.player.headshot && (
            <img src={roster.player.headshot} alt={roster.player.full_name} className="h-8" />
          )}
        </div>
        <p className="pl-2 min-w-[12em] font-bold">{roster?.player?.full_name}</p>
        <p className="min-w-[3em] text-right text-muted-foreground">{roster.player.team}</p>
        <p className="text-muted-foreground">&nbsp; • &nbsp;</p>
        <p className="min-w-[3em] text-left text-muted-foreground">{roster.player.position} {roster.position_rank}</p>
      </TableCell>
      <TableCell className="py-1 text-center">
        {roster.nfl_game.is_bye ? (
          <p className="text-muted-foreground">Bye</p>
        ) : (
          <div className="grid grid-cols-2 gap-2">
            <div className="flex items-center justify-end font-extrabold text-right">
              {roster.player.team === roster.nfl_game.away_team?.id
                ? <span>@ {roster.nfl_game.home_team?.id}</span>
                : <span> {roster.nfl_game.away_team?.id}</span>
              }
            </div>
            <div className="flex items-center text-muted-foreground text-left">
              {roster.nfl_game.day} {roster.nfl_game.time}
            </div>
          </div>
        )}
      </TableCell>
      <TableCell className="py-1">
        <div className="min-w-[14em] flex items-center justify-center">
          <p className="w-[6em] text-right font-extrabold">
            <ShowRank value={roster.player_projection.fp_pos_rank} prepend={roster.player.position} />
          </p>
          <p className="w-[2em] text-center text-muted-foreground">&nbsp; • &nbsp;</p>
          <p className="w-[6em] text-left font-extrabold">
            <ShowPoints value={roster.player_projection.fp_points} />
            <span className="pl-2 text-xs text-muted-foreground">
              {roster.fp_diff ? `(${roster.fp_diff})` : ''}
            </span>
          </p>
        </div>
      </TableCell>
      <TableCell className="py-1 text-center">
        <p className="font-extrabold">
          <ShowPoints value={roster.player_projection.espn_points} />
        </p>
        <p className="pl-2 text-xs text-muted-foreground">
          {roster.espn_diff ? `(${roster.espn_diff})` : ''}
        </p>
      </TableCell>
      <TableCell className="py-1 text-right">
        <p className="font-extrabold text-right">
          <ShowPoints value={roster.fantasy_points} />
        </p>
      </TableCell>
    </TableRow>
  );
}
