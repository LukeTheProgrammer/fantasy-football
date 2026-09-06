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
      return `${draft.league.platform} sync failed`;
    }

    if (sync.stopped?.reason === 'completed') {
      return 'Draft complete';
    }

    return '';
  }

  if (sync.syncedAt) {
    return `Last pick ${new Date(sync.syncedAt).toLocaleTimeString()}`;
  }

  return '';
}

/**
 * Start and stop the pull of picks from the league's platform.
 *
 * Recording by hand stays open either way: a platform publishes a pick a beat
 * after the room hears it, and a player who fails to resolve never arrives at
 * all, so the skipped count is shown rather than left to be noticed by its
 * absence.
 */
export function DraftSyncToggle({ draft, sync }: DraftSyncToggleProps) {
  const active = !!draft.is_active;
  const failed = sync.stopped?.reason === 'failed';
  const failMsg = failed ? (sync?.stopped?.message ?? 'suck a butt') : null;
  const pulse = [
    'size-2 rounded-full',
    active ? 'animate-pulse bg-emerald-500' : null,
    !active && failed ? 'bg-destructive' : null,
    !active && !failed ? 'bg-muted-foreground/40' : null,
  ];

  const toggle = () => {
    const options = { preserveScroll: true, preserveState: true };

    if (draft.is_active) {
      router.delete(route('drafts.sync.destroy', draft.id), options);
    } else {
      router.post(route('drafts.sync.store', draft.id), {}, options);
    }
  };

  return (
    <div className="flex items-center justify-end gap-2">
      <div className="flex gap-2 text-[10px] leading-tight">
        {failMsg && <span className="text-muted-foreground">{failMsg}</span>}
        {sync.skipped > 0 && <span className="text-destructive">{sync.skipped} pick(s) unmatched</span>}
        <span className="font-medium">{statusLabel(draft, sync)}</span>
      </div>

      <Button variant="outline" size="sm" onClick={toggle}>
        Sync
        <span className={cn(pulse)} aria-hidden />
      </Button>
    </div>
  );
}
