import { Draft } from '@/types/models';

import { getDraftUserMember } from '@/modules/drafts/helpers/getDraftUserMember';

export function isUserDraftAdmin(draft: Draft, userId: number) {
  return getDraftUserMember(draft, userId)?.is_admin || false;
}
