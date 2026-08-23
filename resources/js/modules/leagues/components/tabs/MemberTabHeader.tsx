import { TeamAvatar } from '@/modules/leagues/components/TeamAvatar';
import { type LeagueMemberResource, type LeagueResource } from '@/types/resources';

interface MemberTabHeaderProps {
  league: LeagueResource;
  selectedMember: LeagueMemberResource | null;
}

export function MemberTabHeader({ league, selectedMember }: MemberTabHeaderProps) {
  if (selectedMember === null) {
    return <div></div>;
  }

  return (
    <div className="mb-2 flex w-full items-center justify-between">
      <div className="flex items-center justify-start space-x-2">
        <TeamAvatar member={selectedMember || league.members[0]} />
        <div>
          <h4 className="text-lg font-semibold">{selectedMember?.team_name}</h4>
          <p className="text-xs text-muted-foreground">{selectedMember?.owner_name}</p>
        </div>
      </div>
      <div className="align-center flex justify-end space-x-2 pr-1">
        <div className="min-w-[8em] pr-6">
          <p className="text-xs text-muted-foreground">Points For</p>
          <p>
            <span className="text-lg font-extrabold">{selectedMember.points_for}</span>
            <span className="pl-2 text-xs text-muted-foreground"> ({selectedMember.points_for_rank})</span>
          </p>
        </div>
        <div className="min-w-[8em]">
          <p className="text-xs text-muted-foreground">Points Against</p>
          <p>
            <span className="text-lg font-extrabold">{selectedMember.points_against}</span>
            <span className="pl-2 text-xs text-muted-foreground"> ({selectedMember.points_against_rank})</span>
          </p>
        </div>
        <div className="min-w-[3em] text-right">
          <p className="text-xs text-muted-foreground">Record</p>
          <p className="text-lg font-extrabold">
            {selectedMember.wins} &nbsp; - &nbsp;
            {selectedMember.losses}
            <>{selectedMember.ties > 0 ? ` &nbsp; - &nbsp;${selectedMember.ties}` : ''}</>
          </p>
        </div>
      </div>
    </div>
  );
}
