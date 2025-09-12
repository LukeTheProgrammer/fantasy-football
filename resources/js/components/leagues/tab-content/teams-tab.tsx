import { type League } from '@/types/models';
import LeagueMemberManager from '@/components/form/member-manager';

interface TeamsTabProps {
  league: League;
}

export default function TeamsTab({ league }: TeamsTabProps) {
  return (
    <div className="mb-8 rounded-lg border bg-card">
      <div className="border-b p-6">
        <LeagueMemberManager
          members={league.members}
          maxTeams={league.team_count}
          onMembersChange={() => {}}
        />
      </div>
    </div>
  );
}
