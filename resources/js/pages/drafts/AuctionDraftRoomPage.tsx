import { Heading } from '@/common/heading/Heading';
import { DraftSidePanel } from '@/modules/drafts/components/DraftSidePanel';
import { DraftSyncToggle } from '@/modules/drafts/components/DraftSyncToggle';
import { MarketPulse } from '@/modules/drafts/components/MarketPulse';
import { NominatedPlayer } from '@/modules/drafts/components/NominatedPlayer';
import { PlayerBoard } from '@/modules/drafts/components/PlayerBoard';
import { TeamBudgets } from '@/modules/drafts/components/TeamBudgets';
import { useDraftPickStream } from '@/modules/drafts/helpers/useDraftPickStream';
import { usePersistentState } from '@/modules/drafts/helpers/usePersistentState';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import { type AuctionBudget, type AuctionMarket, type AuctionPlayer, type AuctionTeam, type Draft, type RosterSlot } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';

interface AuctionDraftRoomProps extends PageProps {
  draft: Draft;
  players: AuctionPlayer[];
  /** Inflation and position scarcity across the room. */
  market: AuctionMarket;
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

export default function AuctionDraftRoom({ draft, players, market, teams, rosters, budget }: AuctionDraftRoomProps) {
  // Filters are held in storage: a pick reloads the page, and retyping the
  // search or reselecting the position every time costs the seconds that
  // matter mid auction.
  const [search, setSearch] = usePersistentState(`draft.${draft.id}.search`, '');
  const [position, setPosition] = usePersistentState<string | null>(`draft.${draft.id}.position`, null);
  const [showPicked, setShowPicked] = usePersistentState(`draft.${draft.id}.showPicked`, false);
  const [nominatedId, setNominatedId] = useState<number | null>(null);
  const [activeIndex, setActiveIndex] = useState(0);
  const [selectedTeamId, setSelectedTeamId] = useState<number | null>(null);

  // Picks made on ESPN arrive here on their own while the sync is running.
  const sync = useDraftPickStream(draft.id);

  const searchRef = useRef<HTMLInputElement | null>(null);
  const teamRef = useRef<HTMLButtonElement | null>(null);

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

  // The best man left at the same position, so the bar can say what the player
  // up actually costs over letting him go.
  const nextBest = useMemo(() => {
    if (!nominated) {
      return null;
    }

    return (
      players
        .filter((player) => player.drafted_by === null && player.position_id === nominated.position_id && player.player_id !== nominated.player_id)
        .sort((a, b) => (a.rank ?? Infinity) - (b.rank ?? Infinity))[0] ?? null
    );
  }, [players, nominated]);

  const picks = useMemo(() => players.filter((player) => player.drafted_by !== null).sort((a, b) => (b.pick_id ?? 0) - (a.pick_id ?? 0)), [players]);

  const teamsById = useMemo(() => new Map(teams.map((team) => [team.id, team])), [teams]);

  const selectedTeam = selectedTeamId !== null ? (teamsById.get(selectedTeamId) ?? null) : null;

  // A filter change can leave the keyboard past the end of the board.
  useEffect(() => {
    setActiveIndex((index) => Math.min(index, Math.max(0, available.length - 1)));
  }, [available.length]);

  // The auction moves faster than a mouse does: slash to search, arrows to walk
  // the board, Enter to put a man up and land in the price box.
  useEffect(() => {
    const nominateActive = () => {
      const player = available[activeIndex];

      if (!player) {
        return;
      }

      setNominatedId(player.player_id);
      // The bar remounts on nomination, so the focus has to wait for it. It
      // lands on the team dropdown, and Tab runs on to the price and the pick.
      window.setTimeout(() => teamRef.current?.focus(), 0);
    };

    const handleKeyDown = (event: KeyboardEvent) => {
      const target = event.target as HTMLElement | null;
      const inSearch = target === searchRef.current;
      // The pick form owns its own keys once focus is in it: Enter and the
      // arrows belong to the team dropdown, not to the board behind it.
      // An open dropdown renders in a portal outside the form, so it is matched
      // on its own wrapper rather than by walking up to the form.
      const inPickForm = target?.closest('[data-pick-form], [data-radix-popper-content-wrapper]') != null;
      const typing = !inSearch && (inPickForm || target?.tagName === 'INPUT' || target?.tagName === 'TEXTAREA' || target?.isContentEditable === true);

      if (event.key === 'Escape') {
        // Escape in the search box empties it and stays there, so the board
        // comes back without reaching for the mouse. Only an already empty box
        // gives the key up to the room behind it.
        if (inSearch && search !== '') {
          event.preventDefault();
          setSearch('');

          return;
        }

        // Escape inside the form is closing the dropdown, not abandoning the
        // player, so the nomination only clears from outside it.
        if (!inPickForm) {
          target?.blur();
          setNominatedId(null);
        }

        return;
      }

      if (typing || event.metaKey || event.ctrlKey || event.altKey) {
        return;
      }

      if (event.key === '/') {
        event.preventDefault();
        searchRef.current?.focus();
        searchRef.current?.select();

        return;
      }

      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        setActiveIndex((index) => {
          const next = index + (event.key === 'ArrowDown' ? 1 : -1);

          return Math.min(Math.max(next, 0), Math.max(0, available.length - 1));
        });

        return;
      }

      if (event.key === 'Enter') {
        event.preventDefault();
        nominateActive();
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [available, activeIndex, search, setSearch]);

  const handleUndo = (pickId: number) => {
    router.delete(route('drafts.picks.destroy', [draft.id, pickId]), { preserveScroll: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} Auction Room`} />

      <div className="flex-1 space-y-2 p-6">
        <div className="flex flex-col items-start justify-between gap-2 md:flex-row md:items-center">
          <Heading title={`${draft.league.name} ${draft.league.season}`} containerClass="mb-0" headingClass="mb-0" />
          <div className="flex items-center gap-4">
            <DraftSyncToggle draft={draft} sync={sync} />
            <MarketPulse market={market} availableCount={available.length} />
          </div>
        </div>

        {/* Desktop only: one fixed height row, budgets down the left, the
            nominated player as a bar over the board and the pick list. */}
        <div className="grid h-[calc(100vh-14rem)] grid-cols-[1fr_3fr_2fr] grid-rows-[auto_1fr] gap-4">
          <div className="row-span-2 min-h-0 overflow-auto pr-1">
            <TeamBudgets
              teams={teams}
              selectedTeamId={selectedTeamId}
              onSelect={(teamId) => setSelectedTeamId(selectedTeamId === teamId ? null : teamId)}
            />
          </div>

          <div className="col-span-2 h-[6.5rem]">
            <NominatedPlayer
              key={nominated?.player_id ?? 'none'}
              player={nominated}
              teams={teams}
              draftId={draft.id}
              nextBest={nextBest}
              teamRef={teamRef}
              onPicked={() => {
                setNominatedId(null);
                // The next nomination is a fresh search, so the sold player's
                // name does not have to be cleared by hand first.
                setSearch('');
              }}
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
            activeIndex={activeIndex}
            onActiveIndexChange={setActiveIndex}
            searchRef={searchRef}
          />

          <DraftSidePanel
            draftId={draft.id}
            picks={picks}
            teams={teams}
            teamsById={teamsById}
            positions={market.positions}
            position={position}
            onPositionChange={setPosition}
            budget={budget}
            selectedTeam={selectedTeam}
            selectedTeamSlots={selectedTeam ? (rosters[String(selectedTeam.id)] ?? []) : []}
            onClearTeam={() => setSelectedTeamId(null)}
            onUndo={handleUndo}
          />
        </div>
      </div>
    </AppLayout>
  );
}
