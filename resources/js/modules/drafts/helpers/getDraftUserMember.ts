import { Draft } from '@/types/models';

export function getDraftUserMember(draft: Draft, userId: number) {
  return draft?.league?.members?.find((member) => member.user_id === userId);
}
