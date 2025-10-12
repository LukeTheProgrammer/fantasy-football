import { type LeagueResource } from '@/types/resources';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import DraftAuction from '@/components/leagues/tab-content/draft-auction';
import DraftSnake from '@/components/leagues/tab-content/draft-snake';

interface DraftTabProps {
  league: LeagueResource;
}

export default function DraftTab({ league }: DraftTabProps) {
  const draft = league.draft;
  const playersDrafted = draft?.picks.filter(p => p.player_id !== null).length || 0;
  const totalPlayers = draft?.picks.length || 0;

  return (
    <div>
      <div className="mb-8 rounded-lg border bg-card">
        <div className="border-b p-6 grid grid-cols-3">
          <div className="text-left">
            <h2 className="text-lg font-semibold">{league.name} {league.season} Draft</h2>
            <p>{draft.draft_type === 'snake' ? 'Snake' : 'Auction'}</p>
          </div>
          <div className="flex items-center justify-center">
            {playersDrafted > 0 && totalPlayers > 0 && (
              <p>{playersDrafted} / {totalPlayers} Players Drafted</p>
            )}
          </div>
          <div className="flex items-center justify-end">
            {draft.is_completed === false && (
              <Link href={route('drafts.draft-room', league.draft.id)}>
                <Button variant="outline" className="text-right">
                  Enter Draft Room
                </Button>
              </Link>
            )}
          </div>
        </div>
      </div>

      <div className="mb-8 rounded-lg border bg-card">
        <div className="border-b p-6">
          {draft.draft_type === 'snake' && <DraftSnake league={league} />}
          {draft.draft_type === 'auction' && <DraftAuction league={league} />}
        </div>
      </div>
    </div>
  );
}
