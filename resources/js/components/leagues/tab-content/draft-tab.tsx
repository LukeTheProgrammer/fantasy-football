import { type LeagueResource } from '@/types/resources';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

interface DraftTabProps {
  league: LeagueResource;
}

export default function DraftTab({ league }: DraftTabProps) {
  // const { auth } = usePage<SharedData>().props;
  // const userId = auth.user.id;
  // const userIsAdmin = isUserLeagueAdmin(league, userId);

  const draft = league.draft;

  const playersDrafted = draft?.picks.filter(p => p.player_id !== null).length || 0;
  const totalPlayers = draft?.picks.length || 0;

  return (
    <div>
      <div className="mb-8 rounded-lg border bg-card">
        <div className="border-b p-6 grid grid-cols-3">
          <div className="text-left">
            <h2 className="text-lg font-semibold">Draft</h2>
            <p>{league.name} {league.year} Draft</p>
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
          {draft.picks.map((pick) => (
            <div key={pick.id}>
              <p>Round {pick.round} - Pick {pick.pick_number}</p>
              <p>{JSON.stringify(pick.player)}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
