import { cn } from '@/common/helpers/cn';
import { Button } from '@/components/ui/button';
import { type DraftSyncState } from '@/modules/drafts/helpers/useDraftPickStream';
import { type Draft } from '@/types/models';
import { router } from '@inertiajs/react';

interface DraftSyncToggleProps {
  draft: Draft;
  sync: DraftSyncState;
}

function statusLabel(draft: Draft, sync: DraftSyncState): string {
  if (!draft.is_active) {
    if (sync.stopped?.reason === 'failed') {
      return 'ESPN sync failed';
    }

    if (sync.stopped?.reason === 'completed') {
      return 'Draft complete';
    }

    return 'ESPN sync off';
  }

  if (sync.syncedAt) {
    return `Last pick ${new Date(sync.syncedAt).toLocaleTimeString()}`;
  }

  return 'Watching ESPN';
}

/**
 * Start and stop the pull of picks from ESPN.
 *
 * The manual sale form stays either way: ESPN publishes a pick a beat after the
 * room hears it, and a player who fails to resolve never arrives at all, so the
 * skipped count is shown rather than left to be noticed by its absence.
 */
export function DraftSyncToggle({ draft, sync }: DraftSyncToggleProps) {
  const failed = sync.stopped?.reason === 'failed';

  const toggle = () => {
    const options = { preserveScroll: true, preserveState: true };

    if (draft.is_active) {
      router.delete(route('drafts.sync.destroy', draft.id), options);

      return;
    }

    router.post(route('drafts.sync.store', draft.id), {}, options);
  };

  return (
    <div className="flex items-center gap-2">
      <span
        className={cn('size-2 rounded-full', draft.is_active ? 'animate-pulse bg-emerald-500' : failed ? 'bg-destructive' : 'bg-muted-foreground/40')}
        aria-hidden
      />

      <div className="text-xs leading-tight">
        <p className="font-medium">{statusLabel(draft, sync)}</p>
        {sync.skipped > 0 && <p className="text-destructive">{sync.skipped} pick(s) unmatched — enter by hand</p>}
        {failed && sync.stopped?.message && <p className="max-w-48 truncate text-muted-foreground">{sync.stopped.message}</p>}
      </div>

      <Button variant={draft.is_active ? 'outline' : 'default'} size="sm" onClick={toggle}>
        {draft.is_active ? 'Stop sync' : 'Sync from ESPN'}
      </Button>
    </div>
  );
}
