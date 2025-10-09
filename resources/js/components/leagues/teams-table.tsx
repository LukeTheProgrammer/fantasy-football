import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useEffect, useState } from 'react';

interface Team {
  id: number;
  team_name: string;
  team_logo: string;
  is_admin: boolean;
}

interface TeamsProps {
  leagueId: string|number;
}

export default function TeamsTable({ leagueId }: TeamsProps) {
  // const [teams, setTeams] = useState<Team[]>([]);

  // useEffect(() => {
  //   const fetchDrafts = () => {
  //     axios
  //       .get(`/api/leagues/${leagueId}/drafts`)
  //       .then((response) => {
  //         setDrafts(response.data);
  //       })
  //       .catch((error) => {
  //         console.error(error);
  //       });
  //   };

  //   fetchDrafts();
  // }, [leagueId]);

  // if (drafts.length === 0) {
  //   return (
  //     <div className="py-12 text-center">
  //       <h3 className="mb-2 text-lg font-medium">You haven't created any drafts yet</h3>
  //       <p className="mb-6 text-gray-500 dark:text-gray-400">Create your first draft to get started</p>
  //       <Link href={route('drafts.create')}>
  //         <Button>Create New Draft</Button>
  //       </Link>
  //     </div>
  //   );
  // }

  // return (
  //   <Table>
  //     <TableHeader>
  //       <TableRow>
  //         <TableHead>Season</TableHead>
  //         <TableHead>Type</TableHead>
  //         <TableHead>Date</TableHead>
  //         <TableHead>Status</TableHead>
  //       </TableRow>
  //     </TableHeader>
  //     <TableBody>
  //       {drafts.map((draft) => (
  //         <TableRow key={draft.id}>
  //           <TableCell>{draft.league_season.season}</TableCell>
  //           <TableCell>{draft.draft_type}</TableCell>
  //           <TableCell>{draft.draft_date}</TableCell>
  //           <TableCell>{draft.is_completed ? 'Completed' : 'Upcoming'}</TableCell>
  //         </TableRow>
  //       ))}
  //     </TableBody>
  //   </Table>
  // );
}
