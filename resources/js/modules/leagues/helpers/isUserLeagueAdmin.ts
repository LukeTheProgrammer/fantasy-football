import { League } from '@/types/models';

import { getLeagueUserMember } from '@/modules/leagues/helpers/getLeagueUserMember';

export function isUserLeagueAdmin(league: League, userId: number) {
  return getLeagueUserMember(league, userId)?.is_admin || false;
}
