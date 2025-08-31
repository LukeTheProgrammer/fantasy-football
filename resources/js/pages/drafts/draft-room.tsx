import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { Plus } from 'lucide-react';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft, type LeagueMember, type DraftRanking, type User, type Player } from '@/types/models';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PositionBadge } from '@/components/position-badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { useState, useMemo } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Draft Room',
    href: '/draft-room',
  },
];

interface DraftIndexProps extends PageProps {
  draft: Draft;
  availablePlayers: DraftRanking[];
}

export default function DraftRoom({ draft, availablePlayers }: DraftIndexProps) {
  const [filterText, setFilterText] = useState('');

  const filteredPlayers = useMemo(() => {
    if (!filterText.trim()) {
      return availablePlayers;
    }

    const searchTerm = filterText.toLowerCase();
    return availablePlayers.filter((draftRank) => {
      const player = draftRank.player;
      return (
        player.full_name.toLowerCase().includes(searchTerm) ||
        player.position.abbreviation.toLowerCase().includes(searchTerm) ||
        player.team.abbreviation.toLowerCase().includes(searchTerm)
      );
    });
  }, [availablePlayers, filterText]);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Draft Room" />

      <div className="flex-1 p-8">
        <Heading title={`${draft.league.name} ${draft.league.year} Draft Room`}/>

        <div className="mb-8">
          <div className="flex gap-2 overflow-x-auto">
            {draft.league.members.map((member) => (
              <div key={member.id} className="flex-1 min-w-0 rounded-lg border bg-card p-3">
                <div className="text-center">
                  <p className="text-xs font-semibold truncate">{member.team_name}</p>
                  <p className="text-xs text-muted-foreground truncate">{member.owner_name}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="grid grid-cols-1 w-full">
            <Card>
              <CardHeader>
                <CardTitle>
                  <div className="flex items-center justify-between gap-2">
                    <div className="grow-2">
                      <span>Players</span>
                    </div>
                    <div className="grow-1">
                      <Input 
                        placeholder="Filter players..." 
                        value={filterText}
                        onChange={(e) => setFilterText(e.target.value)}
                      />
                    </div>
                  </div>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>&nbsp;</TableHead>
                      <TableHead>Rank</TableHead>
                      <TableHead>Player</TableHead>
                      <TableHead>Position</TableHead>
                      <TableHead>Team</TableHead>
                      <TableHead>Avg Rank</TableHead>
                      <TableHead>Avg Value</TableHead>
                      <TableHead>FP Ranking</TableHead>
                      <TableHead>FP Tier</TableHead>
                      <TableHead>FP ADP</TableHead>
                      <TableHead>FP ADV</TableHead>
                      <TableHead>FP ECR vs ADP</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredPlayers.map((draftRank, i) => (
                      <TableRow key={draftRank.id}>
                        <TableCell><Checkbox /></TableCell>
                        <TableCell>{i + 1}</TableCell>
                        <TableCell>{draftRank.player.full_name}</TableCell>
                        <TableCell><PositionBadge position={draftRank.player.position.abbreviation} /></TableCell>
                        <TableCell>{draftRank.player.team.abbreviation}</TableCell>
                        <TableCell>{draftRank.average_rank}</TableCell>
                        <TableCell>{draftRank.average_value}</TableCell>
                        <TableCell>{draftRank.fp_standard_ranking}</TableCell>
                        <TableCell>{draftRank.fp_standard_tier}</TableCell>
                        <TableCell>{draftRank.fp_standard_adp}</TableCell>
                        <TableCell>{draftRank.fp_standard_adv}</TableCell>
                        <TableCell>{draftRank.fp_standard_ecr_vs_adp}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </div>
      </div>
    </AppLayout>
  );
}
