import { useEcho } from '@laravel/echo-react';

interface PlayerNominatedPayload {
  player_id: number;
  league_member_id: number | null;
  amount: number;
  nominated_at: string;
}

/**
 * The player ESPN has up for bid.
 *
 * The room follows the ESPN draft rather than leading it, so a nomination
 * arriving here replaces whatever the board had selected: the useful thing
 * mid auction is to be looking at the player being bid on without having to
 * find him.
 */
export function useDraftNominationStream(draftId: number, onNominated: (playerId: number) => void): void {
  useEcho<PlayerNominatedPayload>(
    `draft.${draftId}`,
    '.PlayerNominated',
    (payload) => {
      onNominated(payload.player_id);
    },
    [draftId, onNominated],
  );
}
