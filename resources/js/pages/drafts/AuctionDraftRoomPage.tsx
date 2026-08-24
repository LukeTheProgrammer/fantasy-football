import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { BudgetPlan } from '@/modules/drafts/components/BudgetPlan';
import { DraftPicks } from '@/modules/drafts/components/DraftPicks';
import { NominatedPlayer } from '@/modules/drafts/components/NominatedPlayer';
import { PlayerBoard } from '@/modules/drafts/components/PlayerBoard';
import { TeamBudgets } from '@/modules/drafts/components/TeamBudgets';
import { TeamRoster } from '@/modules/drafts/components/TeamRoster';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import { type AuctionBudget, type AuctionPlayer, type AuctionTeam, type Draft, type RosterSlot } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';

interface AuctionDraftRoomProps extends PageProps {
  draft: Draft;
  players: AuctionPlayer[];
  teams: AuctionTeam[];
  /** Roster slots per league member id. */
  rosters: Record<string, RosterSlot[]>;
  /** The signed in user's own plan, when they have a team in this league. */
  budget: AuctionBudget | null;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Drafts',
    href: '/drafts',
  },
  {
    title: 'Draft Room',
    href: '#',
  },
];

export default function AuctionDraftRoom({ draft, players, teams, rosters, budget }: AuctionDraftRoomProps) {
  const [search, setSearch] = useState('');
  const [position, setPosition] = useState<string | null>(null);
  const [nominatedId, setNominatedId] = useState<number | null>(null);
  const [showPicked, setShowPicked] = useState(false);
  const [selectedTeamId, setSelectedTeamId] = useState<number | null>(null);
  const [showBudget, setShowBudget] = useState(false);

  const available = useMemo(() => {
    const term = search.trim().toLowerCase();

    return players.filter((player) => {
      if (!showPicked && player.drafted_by !== null) {
        return false;
      }

      if (position && player.position_id !== position) {
        return false;
      }

      if (!term) {
        return true;
      }

      return (
        player.full_name?.toLowerCase().includes(term) ||
        player.position_id?.toLowerCase().includes(term) ||
        player.team_id?.toLowerCase().includes(term)
      );
    });
  }, [players, search, position, showPicked]);

  const nominated = useMemo(() => players.find((player) => player.player_id === nominatedId) ?? null, [players, nominatedId]);

  const picks = useMemo(() => players.filter((player) => player.drafted_by !== null).sort((a, b) => (b.pick_id ?? 0) - (a.pick_id ?? 0)), [players]);

  const teamsById = useMemo(() => new Map(teams.map((team) => [team.id, team])), [teams]);

  const selectedTeam = selectedTeamId !== null ? (teamsById.get(selectedTeamId) ?? null) : null;

  const handleUndo = (pickId: number) => {
    router.delete(route('drafts.picks.destroy', [draft.id, pickId]), { preserveScroll: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} Auction Room`} />

      <div className="flex-1 space-y-2 p-6">
        <div className="flex flex-col items-start justify-between gap-2 md:flex-row md:items-center">
          <Heading title={`${draft.league.name} ${draft.league.season}`} description={`Auction cheat sheet · $${draft.auction_budget} per team`} />
          <p className="text-sm text-muted-foreground tabular-nums">
            {picks.length} picks · {available.length} on the board
          </p>
        </div>

        {/* Desktop only: one fixed height row, budgets down the left, the
            nominated player as a bar over the board and the pick list. */}
        <div className="grid h-[calc(100vh-14rem)] grid-cols-[1fr_3fr_2fr] grid-rows-[auto_1fr] gap-4">
          <div className="row-span-2 min-h-0 overflow-auto pr-1">
            <TeamBudgets
              teams={teams}
              selectedTeamId={selectedTeamId}
              onSelect={(teamId) => {
                setShowBudget(false);
                setSelectedTeamId(selectedTeamId === teamId ? null : teamId);
              }}
            />
          </div>

          <div className="col-span-2 h-[6.5rem]">
            <NominatedPlayer
              key={nominated?.player_id ?? 'none'}
              player={nominated}
              teams={teams}
              draftId={draft.id}
              onPicked={() => setNominatedId(null)}
            />
          </div>

          <PlayerBoard
            players={available}
            teamsById={teamsById}
            season={draft.league.season}
            nominatedId={nominatedId}
            onNominate={setNominatedId}
            search={search}
            onSearchChange={setSearch}
            position={position}
            onPositionChange={setPosition}
            showPicked={showPicked}
            onShowPickedChange={setShowPicked}
          />

          {/* One column, three jobs: your own plan, a team's roster while you
              size up a bid, and the running list of picks otherwise. */}
          <div className="flex min-h-0 flex-col gap-2">
            {budget && (
              <div className="flex gap-1">
                <Button
                  size="sm"
                  variant={showBudget ? 'default' : 'outline'}
                  onClick={() => {
                    setShowBudget(true);
                    setSelectedTeamId(null);
                  }}
                >
                  My budget
                </Button>
                <Button
                  size="sm"
                  variant={!showBudget && !selectedTeam ? 'default' : 'outline'}
                  onClick={() => {
                    setShowBudget(false);
                    setSelectedTeamId(null);
                  }}
                >
                  Picks
                </Button>
              </div>
            )}

            <div className="min-h-0 flex-1">
              {showBudget && budget ? (
                <BudgetPlan budget={budget} draftId={draft.id} />
              ) : selectedTeam ? (
                <TeamRoster team={selectedTeam} slots={rosters[String(selectedTeam.id)] ?? []} onClose={() => setSelectedTeamId(null)} />
              ) : (
                <DraftPicks players={picks} teams={teams} teamsById={teamsById} draftId={draft.id} onUndo={handleUndo} />
              )}
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
