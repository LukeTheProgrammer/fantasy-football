import { League } from '@/types/models';

export function getLeagueUserMember(league: League, userId: number) {
  return league?.members?.find((member) => member.user_id === userId);
}
