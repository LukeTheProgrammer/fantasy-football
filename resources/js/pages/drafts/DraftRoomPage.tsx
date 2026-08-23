import { AppLayout } from '@/pages/layouts/AppLayout';
import { Heading } from '@/common/heading/Heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { Plus } from 'lucide-react';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft, type LeagueMember, type DraftRanking, type User, type Player } from '@/types/models';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
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
        player.position_id.toLowerCase().includes(searchTerm) ||
        (player.team_id?.toLowerCase().includes(searchTerm) ?? false)
      );
    });
  }, [availablePlayers, filterText]);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Draft Room" />

      <div className="flex-1 p-8">
        <Heading title={`${draft.league.name} ${draft.league.season} Draft Room`}/>

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
                      <TableHead>Source</TableHead>
                      <TableHead>PPR</TableHead>
                      <TableHead>Tier</TableHead>
                      <TableHead>ADP</TableHead>
                      <TableHead>ADV</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredPlayers.map((draftRank) => (
                      <TableRow key={draftRank.id}>
                        <TableCell><Checkbox /></TableCell>
                        <TableCell>{draftRank.rank}</TableCell>
                        <TableCell>{draftRank.player.full_name}</TableCell>
                        <TableCell><PositionBadge position={draftRank.player.position} /></TableCell>
                        <TableCell>{draftRank.player.team?.abbreviation}</TableCell>
                        <TableCell>{draftRank.source}</TableCell>
                        <TableCell>{draftRank.ppr}</TableCell>
                        <TableCell>{draftRank.tier}</TableCell>
                        <TableCell>{draftRank.adp}</TableCell>
                        <TableCell>{draftRank.adv}</TableCell>
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
