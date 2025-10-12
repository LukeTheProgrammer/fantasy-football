import MemberTabHeader from '@/components/leagues/tab-content/member-tab-header';
import { type LeagueResource, type LeagueMemberResource, type LeagueRosterResource } from '@/types/resources';
import ShowPoints from '@/components/show-points';
import ShowRank from '@/components/show-rank';
import RostersTableRow from '@/components/leagues/tab-content/rosters-table-row';
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

interface Lineup {
  starters: LeagueRosterResource[];
  bench: LeagueRosterResource[];
}

export default function ShowLeague({ league, selectedMember, selectedWeek }: RostersTabProps) {

  const getLineup = (memberRosters: Record<string, LeagueRosterResource[]>): Lineup => {
    if (!memberRosters || Object.keys(memberRosters).length === 0) {
      return { starters: [], bench: [] };
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
    const roster = memberRosters[weekNum] || [];

    const lineup: Lineup = {
      starters: [] as LeagueRosterResource[],
      bench: [] as LeagueRosterResource[],
    };

    roster.forEach(rosterSlot => {
      if (rosterSlot.lineup_slot_id === 20) {
        positions[rosterSlot.player.position].push(rosterSlot);
      } else {
        lineup.starters.push(rosterSlot);
      }
    });

    Object.entries(positions).forEach(([, players]) => {
      lineup.bench.push(...players);
    });

    return lineup;
  }

  if (selectedMember === null) {
    return (
      <div>
        <h1>No team selected</h1>
      </div>
    );
  }

  const lineup = getLineup(selectedMember?.rosters || {});

  return (
    <div className="w-full p-4 mb-8 rounded-lg border bg-card">
      <MemberTabHeader league={league} selectedMember={selectedMember} />

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead className='text-center'>POS</TableHead>
            <TableHead>Player</TableHead>
            <TableHead className='text-center'>Game</TableHead>
            <TableHead className='text-center'>Fantasy Pros</TableHead>
            <TableHead className='text-center'>ESPN</TableHead>
            <TableHead className='text-right'>Points</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {lineup.starters.map((roster) => (
            <RostersTableRow key={roster?.player?.id} roster={roster} />
          ))}
          <TableRow>
            <TableCell className='text-center' colSpan={6}>&nbsp;</TableCell>
          </TableRow>
          {lineup.bench.map((roster) => (
            <RostersTableRow key={roster?.player?.id} roster={roster} />
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
