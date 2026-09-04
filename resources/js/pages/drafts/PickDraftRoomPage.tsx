import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { OnTheClock } from '@/modules/drafts/components/pick/OnTheClock';
import { PickBoard } from '@/modules/drafts/components/pick/PickBoard';
import { PickRosters } from '@/modules/drafts/components/pick/PickRosters';
import { TeamColumn } from '@/modules/drafts/components/pick/TeamColumn';
import { isUserDraftAdmin } from '@/modules/drafts/helpers/isUserDraftAdmin';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft, type DraftRanking } from '@/types/models';
import { type DraftClock, type TeamRoster } from '@/types/picks';
import { PageProps } from '@inertiajs/core';
import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

interface PickDraftRoomProps extends PageProps {
  clock: DraftClock;
  draft: Draft;
  players: DraftRanking[];
  rosters: TeamRoster[];
}

export default function PickDraftRoom({ clock, draft, players, rosters }: PickDraftRoomProps) {
  const { auth } = usePage<SharedData>().props;
  const [recording, setRecording] = useState(false);
  const [selectedTeamId, setSelectedTeamId] = useState<number | null>(null);

  const breadcrumbs: BreadcrumbItem[] = [
    { title: draft.league.name, href: `/leagues/${draft.league.id}` },
    { title: 'Draft Room', href: '#' },
  ];

  const canRecord = isUserDraftAdmin(draft, auth.user.id);

  // Picks are traded in this league, so how many a team has left is counted
  // from the order itself rather than assumed to be the same for everyone.
  const remainingByMember = useMemo(() => {
    const byExternalId = new Map(rosters.map((roster) => [roster.external_id, roster.league_member_id]));
    const remaining: Record<number, number> = {};

    (draft.draft_order ?? []).slice(clock.made).forEach((externalId) => {
      const memberId = byExternalId.get(externalId);

      if (memberId !== undefined) {
        remaining[memberId] = (remaining[memberId] ?? 0) + 1;
      }
    });

    return remaining;
  }, [draft.draft_order, clock.made, rosters]);

  // The team on the clock is what the panel shows until one is chosen, so the
  // room defaults to the roster about to be added to.
  const shownTeamId = selectedTeamId ?? clock.current?.league_member_id ?? null;
  const shownRoster = useMemo(() => rosters.find((roster) => roster.league_member_id === shownTeamId) ?? null, [rosters, shownTeamId]);

  const recentPicks = useMemo(() => {
    return rosters
      .flatMap((roster) => roster.picks.map((pick) => ({ ...pick, team_name: roster.team_name })))
      .sort((a, b) => b.overall_pick_number - a.overall_pick_number)
      .slice(0, 10);
  }, [rosters]);

  const draftPlayer = (playerId: number) => {
    setRecording(true);

    router.post(route('drafts.board-picks.store', draft.id), { player_id: playerId }, { preserveScroll: true, onFinish: () => setRecording(false) });
  };

  const undoPick = (pickId: number) => {
    router.delete(route('drafts.board-picks.destroy', [draft.id, pickId]), { preserveScroll: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} Draft Room`} />

      <div className="flex-1 space-y-2 p-6">
        <Heading
          title={`${draft.league.name} ${draft.league.season_id}`}
          description={`${draft.rounds} rounds`}
          containerClass="mb-0"
          headingClass="mb-0"
        />

        {/* Desktop only: one fixed height row, the league down the left, the
            clock as a bar over the board and the roster panel. */}
        <div className="grid h-[calc(100vh-14rem)] grid-cols-[1fr_3fr_2fr] grid-rows-[auto_1fr] gap-4">
          <div className="row-span-2 min-h-0 overflow-auto pr-1">
            <TeamColumn
              onSelect={(memberId) => setSelectedTeamId(selectedTeamId === memberId ? null : memberId)}
              onTheClockMemberId={clock.current?.league_member_id ?? null}
              remainingByMember={remainingByMember}
              rosters={rosters}
              selectedTeamId={selectedTeamId}
            />
          </div>

          <div className="col-span-2">
            <OnTheClock clock={clock} />
          </div>

          <div className="min-h-0 overflow-auto">
            <PickBoard players={players} canRecord={canRecord} recording={recording} onDraft={draftPlayer} />
          </div>

          <div className="min-h-0 space-y-4 overflow-auto pr-1">
            <Card>
              <CardHeader>
                <CardTitle>Recent picks</CardTitle>
              </CardHeader>
              <CardContent>
                {recentPicks.length === 0 && <p className="text-sm text-muted-foreground">Nothing drafted yet.</p>}

                <ul className="space-y-2">
                  {recentPicks.map((pick) => (
                    <li key={pick.pick_id} className="flex items-center justify-between gap-2 text-sm">
                      <span className="min-w-0">
                        <span className="text-muted-foreground tabular-nums">#{pick.overall_pick_number}</span> {pick.full_name}
                        <span className="block text-xs text-muted-foreground">{pick.team_name}</span>
                      </span>
                      {canRecord && (
                        <Button size="sm" variant="ghost" onClick={() => undoPick(pick.pick_id)}>
                          Undo
                        </Button>
                      )}
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>

            <PickRosters roster={shownRoster} onClear={selectedTeamId !== null ? () => setSelectedTeamId(null) : undefined} />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
