import { Heading } from '@/common/heading/Heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import { type Draft, type DraftRanking } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Draft Room',
    href: '/draft-room',
  },
];

interface DraftRoomProps extends PageProps {
  draft: Draft;
  availablePlayers: DraftRanking[];
}

export default function DraftRoom({ draft, availablePlayers }: DraftRoomProps) {
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
        <Heading title={`${draft.league.name} ${draft.league.season} Draft Room`} />

        <div className="mb-8">
          <div className="flex gap-2 overflow-x-auto">
            {draft.league.members.map((member) => (
              <div key={member.id} className="min-w-0 flex-1 rounded-lg border bg-card p-3">
                <div className="text-center">
                  <p className="truncate text-xs font-semibold">{member.team_name}</p>
                  <p className="truncate text-xs text-muted-foreground">{member.owner_name}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="grid w-full grid-cols-1">
          <Card>
            <CardHeader>
              <CardTitle>
                <div className="flex items-center justify-between gap-2">
                  <div className="grow-2">
                    <span>Players</span>
                  </div>
                  <div className="grow">
                    <Input placeholder="Filter players..." value={filterText} onChange={(e) => setFilterText(e.target.value)} />
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
                      <TableCell>
                        <Checkbox />
                      </TableCell>
                      <TableCell>{draftRank.rank}</TableCell>
                      <TableCell>{draftRank.player.full_name}</TableCell>
                      <TableCell>
                        <PositionBadge position={draftRank.player.position} />
                      </TableCell>
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
