import { Button } from '@/components/ui/button';
import { DraftAuction } from '@/modules/leagues/components/tabs/DraftAuction';
import { DraftSnake } from '@/modules/leagues/components/tabs/DraftSnake';
import { type LeagueResource } from '@/types/resources';
import { Link } from '@inertiajs/react';

interface DraftTabProps {
  league: LeagueResource;
}

export function DraftTab({ league }: DraftTabProps) {
  const draft = league.draft;

  if (!draft) {
    return null;
  }

  const playersDrafted = draft.picks.filter((pick) => Boolean(pick.player)).length;
  const totalPlayers = draft.picks.length;

  return (
    <div>
      <div className="mb-8 rounded-lg border bg-card">
        <div className="grid grid-cols-3 border-b p-6">
          <div className="text-left">
            <h2 className="text-lg font-semibold">
              {league.name} {league.season_id} Draft
            </h2>
            <p>{draft.draft_type === 'snake' ? 'Snake' : 'Auction'}</p>
          </div>
          <div className="flex items-center justify-center">
            {playersDrafted > 0 && totalPlayers > 0 && (
              <p>
                {playersDrafted} / {totalPlayers} Players Drafted
              </p>
            )}
          </div>
          <div className="flex items-center justify-end">
            {draft.is_completed === false && (
              <Link href={route('drafts.draft-room', draft.id)}>
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
