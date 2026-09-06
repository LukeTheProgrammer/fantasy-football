import { Heading } from '@/common/heading/Heading';
import { DraftSyncToggle } from '@/modules/drafts/components/DraftSyncToggle';
import { OnTheClock } from '@/modules/drafts/components/pick/OnTheClock';
import { PickBoard } from '@/modules/drafts/components/pick/PickBoard';
import { PickRosters } from '@/modules/drafts/components/pick/PickRosters';
import { getDraftUserMember } from '@/modules/drafts/helpers/getDraftUserMember';
import { useDraftPickStream } from '@/modules/drafts/helpers/useDraftPickStream';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft } from '@/types/models';
import { type BoardPlayer, type DraftClock, type TeamRoster } from '@/types/picks';
import { PageProps } from '@inertiajs/core';
import { Head, router, usePage } from '@inertiajs/react';
import { AlarmClock } from 'lucide-react';
import { useMemo, useState } from 'react';

interface PickDraftRoomProps extends PageProps {
  clock: DraftClock;
  draft: Draft;
  players: BoardPlayer[];
  rosters: TeamRoster[];
}

export default function PickDraftRoom({ clock, draft, players, rosters }: PickDraftRoomProps) {
  const { auth } = usePage<SharedData>().props;
  const [recording, setRecording] = useState(false);
  // Picks made on the platform arrive here while the sync loop is running, and
  // reload the board rather than being patched into it.
  const sync = useDraftPickStream(draft.id);
  const [selectedTeamId, setSelectedTeamId] = useState<number | null>(null);

  const breadcrumbs: BreadcrumbItem[] = [
    { title: draft.league.name, href: `/leagues/${draft.league.id}` },
    { title: 'Draft Room', href: '#' },
  ];

  // Recording is open to any member of the league, the same as the policy
  // behind the route: the board is a personal record of the room, not the
  // draft itself, and the commissioner is rarely the one holding the laptop.
  const canRecord = getDraftUserMember(draft, auth.user.id) !== undefined;

  // The team on the clock is what the panel shows until one is chosen, so the
  // room defaults to the roster about to be added to.
  const shownTeamId = selectedTeamId ?? clock.current?.league_member_id ?? null;
  const shownRoster = useMemo(() => rosters.find((roster) => roster.league_member_id === shownTeamId) ?? null, [rosters, shownTeamId]);

  const draftPlayer = (playerId: number) => {
    setRecording(true);

    router.post(route('drafts.board-picks.store', draft.id), { player_id: playerId }, { preserveScroll: true, onFinish: () => setRecording(false) });
  };

  // Giving up a slot uses it just as a pick does, so the clock moves on.
  const skipPick = () => {
    router.post(route('drafts.board-picks.skip', draft.id), {}, { preserveScroll: true });
  };

  const undoPick = (pickId: number) => {
    router.delete(route('drafts.board-picks.destroy', [draft.id, pickId]), { preserveScroll: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs} actionItem={<DraftSyncToggle draft={draft} sync={sync} />}>
      <Head title={`${draft.league.name} Draft Room`} />

      <div className="flex-1 space-y-2 p-6">
        <Heading title={`${draft.league.name} ${draft.league.season_id}`} rightContent={<HeadingOnTheClock clock={clock} />} containerClass="mb-0" />

        {/* Desktop only: one fixed height row, the round of picks as a bar
            over the board and the roster panel. */}
        <div className="grid h-[calc(100vh-14rem)] grid-cols-[3fr_2fr] grid-rows-[auto_1fr] gap-4">
          <div className="col-span-2">
            <OnTheClock
              canRecord={canRecord}
              clock={clock}
              onSelectTeam={(memberId) => setSelectedTeamId(selectedTeamId === memberId ? null : memberId)}
              onSkip={skipPick}
              onUndo={undoPick}
            />
          </div>

          <div className="min-h-0 overflow-auto">
            <PickBoard
              canRecord={canRecord}
              currentPick={clock.current?.overall_pick_number ?? null}
              draftId={draft.id}
              onDraft={draftPlayer}
              players={players}
              recording={recording}
            />
          </div>

          <div className="min-h-0 space-y-4 overflow-auto pr-1">
            <PickRosters draftId={draft.id} onSelectTeam={setSelectedTeamId} roster={shownRoster} rosters={rosters} />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

function HeadingOnTheClock({ clock }: { clock: DraftClock }) {
  return (
    <div className="flex w-lg items-center justify-start gap-8 rounded-lg border-2 bg-card p-2">
      <AlarmClock />
      <div className="flex grow items-center justify-between gap-2">
        <p className="text-xl font-bold">{clock?.current?.team_name}</p>
        <p className="pt-1 text-xs text-muted-foreground">{clock?.current?.owner_name}</p>
      </div>
    </div>
  );
}
