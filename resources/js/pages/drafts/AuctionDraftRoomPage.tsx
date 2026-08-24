import { Heading } from '@/common/heading/Heading';
import { NominatedPlayer } from '@/modules/drafts/components/NominatedPlayer';
import { PlayerBoard } from '@/modules/drafts/components/PlayerBoard';
import { SoldPlayers } from '@/modules/drafts/components/SoldPlayers';
import { TeamBudgets } from '@/modules/drafts/components/TeamBudgets';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import { type AuctionPlayer, type AuctionTeam, type Draft } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';

interface AuctionDraftRoomProps extends PageProps {
  draft: Draft;
  players: AuctionPlayer[];
  teams: AuctionTeam[];
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

export default function AuctionDraftRoom({ draft, players, teams }: AuctionDraftRoomProps) {
  const [search, setSearch] = useState('');
  const [position, setPosition] = useState<string | null>(null);
  const [nominatedId, setNominatedId] = useState<number | null>(null);
  const [showSold, setShowSold] = useState(false);

  const available = useMemo(() => {
    const term = search.trim().toLowerCase();

    return players.filter((player) => {
      if (!showSold && player.drafted_by !== null) {
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
  }, [players, search, position, showSold]);

  const nominated = useMemo(() => players.find((player) => player.player_id === nominatedId) ?? null, [players, nominatedId]);

  const sold = useMemo(() => players.filter((player) => player.drafted_by !== null).sort((a, b) => (b.pick_id ?? 0) - (a.pick_id ?? 0)), [players]);

  const teamsById = useMemo(() => new Map(teams.map((team) => [team.id, team])), [teams]);

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
            {sold.length} sold · {available.length} on the board
          </p>
        </div>

        {/* Desktop only: one fixed height row, budgets down the left, the
            nominated player as a bar over the board and the sold list. */}
        <div className="grid h-[calc(100vh-14rem)] grid-cols-[1fr_3fr_2fr] grid-rows-[auto_1fr] gap-4">
          <div className="row-span-2 min-h-0 overflow-auto pr-1">
            <TeamBudgets teams={teams} />
          </div>

          <div className="col-span-2 h-[6.5rem]">
            <NominatedPlayer
              key={nominated?.player_id ?? 'none'}
              player={nominated}
              teams={teams}
              draftId={draft.id}
              onSold={() => setNominatedId(null)}
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
            showSold={showSold}
            onShowSoldChange={setShowSold}
          />

          <SoldPlayers players={sold} teamsById={teamsById} onUndo={handleUndo} />
        </div>
      </div>
    </AppLayout>
  );
}
