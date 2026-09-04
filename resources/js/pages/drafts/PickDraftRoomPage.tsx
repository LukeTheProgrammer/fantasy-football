import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { OnTheClock } from '@/modules/drafts/components/OnTheClock';
import { PickBoard } from '@/modules/drafts/components/PickBoard';
import { PickRosters } from '@/modules/drafts/components/PickRosters';
import { isUserDraftAdmin } from '@/modules/drafts/helpers/isUserDraftAdmin';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft, type DraftRanking } from '@/types/models';
import { type DraftClock, type TeamRoster } from '@/types/picks';
import { PageProps } from '@inertiajs/core';
import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

interface PickDraftRoomProps extends PageProps {
  draft: Draft;
  clock: DraftClock;
  players: DraftRanking[];
  rosters: TeamRoster[];
}

export default function PickDraftRoom({ draft, clock, players, rosters }: PickDraftRoomProps) {
  const { auth } = usePage<SharedData>().props;
  const [recording, setRecording] = useState(false);

  const breadcrumbs: BreadcrumbItem[] = [
    { title: draft.league.name, href: `/leagues/${draft.league.id}` },
    { title: 'Draft Room', href: `/drafts/${draft.id}/draft-room` },
  ];

  const canRecord = isUserDraftAdmin(draft, auth.user.id);

  // The last few picks, newest first, so a mistake is undone from the top.
  const recentPicks = useMemo(() => {
    return rosters
      .flatMap((roster) => roster.picks.map((pick) => ({ ...pick, team_name: roster.team_name })))
      .sort((a, b) => b.overall_pick_number - a.overall_pick_number)
      .slice(0, 10);
  }, [rosters]);

  const draftPlayer = (playerId: number) => {
    setRecording(true);

    router.post(
      route('drafts.board-picks.store', draft.id),
      { player_id: playerId },
      {
        preserveScroll: true,
        onFinish: () => setRecording(false),
      },
    );
  };

  const undoPick = (pickId: number) => {
    router.delete(route('drafts.board-picks.destroy', [draft.id, pickId]), { preserveScroll: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Draft Room" />

      <div className="flex-1 p-8">
        <Heading title={`${draft.league.name} ${draft.league.season_id} Draft`} description={`${draft.rounds} rounds`} />

        <OnTheClock clock={clock} />

        <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div className="xl:col-span-2">
            <PickBoard players={players} canRecord={canRecord} recording={recording} onDraft={draftPlayer} />
          </div>

          <div className="space-y-6">
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
                        <span className="text-muted-foreground">#{pick.overall_pick_number}</span> {pick.full_name}
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

            <PickRosters rosters={rosters} onTheClockMemberId={clock.current?.league_member_id ?? null} />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
