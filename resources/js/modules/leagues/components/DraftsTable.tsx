import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Link } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

interface Draft {
  id: number;
  league_id: number;
  draft_date: string;
  draft_type: string;
  is_completed: boolean;
  auction_budget: number;
  current_pick: number;
  current_round: number;
  time_per_pick: number;
  is_active: boolean;
  pivot: {
    is_admin: boolean;
    team_name: string;
  };
  league: {
    name: string;
    season_id: number;
  };
}

interface DraftsProps {
  leagueId: string | number;
}

export function DraftsTable({ leagueId }: DraftsProps) {
  const [drafts, setDrafts] = useState<Draft[]>([]);

  useEffect(() => {
    const fetchDrafts = () => {
      axios
        .get(`/api/leagues/${leagueId}/drafts`)
        .then((response) => {
          setDrafts(response.data);
        })
        .catch((error) => {
          console.error(error);
        });
    };

    fetchDrafts();
  }, [leagueId]);

  if (drafts.length === 0) {
    return (
      <div className="py-12 text-center">
        <h3 className="mb-2 text-lg font-medium">You haven't created any drafts yet</h3>
        <p className="mb-6 text-gray-500 dark:text-gray-400">Create your first draft to get started</p>
        <Link href={route('drafts.create')}>
          <Button>Create New Draft</Button>
        </Link>
      </div>
    );
  }

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Season</TableHead>
          <TableHead>Type</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {drafts.map((draft) => (
          <TableRow key={draft.id}>
            <TableCell>{draft.league?.season_id}</TableCell>
            <TableCell>{draft.draft_type}</TableCell>
            <TableCell>{draft.draft_date}</TableCell>
            <TableCell>{draft.is_completed ? 'Completed' : 'Upcoming'}</TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
