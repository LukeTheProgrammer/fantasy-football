import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { type LeagueMember } from '@/types/models';
import { type LeagueMemberResource, type LeagueTeamResource } from '@/types/resources';

interface TeamAvatarProps {
  member: LeagueMember | LeagueMemberResource | LeagueTeamResource;
}

export function TeamAvatar({ member }: TeamAvatarProps) {
  return (
    <div title={member.team_name}>
      <Avatar>
        {member.team_logo ? <AvatarImage src={member.team_logo} alt={member.team_name} /> : null}
        <AvatarFallback>{member.team_name?.substring(0, 2)?.toUpperCase()}</AvatarFallback>
      </Avatar>
    </div>
  );
}
