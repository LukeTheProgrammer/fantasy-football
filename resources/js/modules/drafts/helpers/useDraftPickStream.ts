import { router } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import { useState } from 'react';

/** What the room last heard from the sync loop. */
export interface DraftSyncState {
  syncedAt: string | null;
  created: number;
  skipped: number;
  stopped: { reason: string; message: string | null } | null;
}

interface PicksSyncedPayload {
  created: number;
  updated: number;
  skipped: number;
  is_completed: boolean;
  synced_at: string;
}

interface SyncStoppedPayload {
  reason: string;
  message: string | null;
  stopped_at: string;
}

/**
 * Picks arriving from the league's platform.
 *
 * The board is built server side, so a pick is applied by asking the page for
 * the props it changed rather than by patching state here: one place decides
 * what a player is worth, and it stays the server.
 */
export function useDraftPickStream(draftId: number): DraftSyncState {
  const [state, setState] = useState<DraftSyncState>({
    syncedAt: null,
    created: 0,
    skipped: 0,
    stopped: null,
  });

  useEcho<PicksSyncedPayload>(
    `draft.${draftId}`,
    '.DraftPicksSynced',
    (payload) => {
      setState((current) => ({
        ...current,
        syncedAt: payload.synced_at,
        created: current.created + payload.created,
        skipped: payload.skipped,
        stopped: null,
      }));

      // The pick room reads a clock the auction room has no prop for, and
      // Inertia ignores the names a page does not publish.
      router.reload({ only: ['clock', 'draft', 'players', 'market', 'teams', 'rosters', 'budget'] });
    },
    [draftId],
  );

  useEcho<SyncStoppedPayload>(
    `draft.${draftId}`,
    '.DraftSyncStopped',
    (payload) => {
      setState((current) => ({ ...current, stopped: { reason: payload.reason, message: payload.message } }));

      router.reload({ only: ['draft'] });
    },
    [draftId],
  );

  return state;
}
