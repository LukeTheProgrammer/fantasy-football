import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { Plus } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { type DraftRanking } from '@/types/models';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Draft Rankings',
    href: '/rankings',
  },
];

interface DraftIndexProps extends PageProps {
  draftRankings: DraftRanking[];
}

export default function Drafts({ draftRankings }: DraftIndexProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Draft Rankings" />

      <div className="flex-1 p-8">
        <Heading
          title="Draft Rankings"
          description="Manage Player Rankings"
          rightContent={(
            <Link href="/rankings/create">
              <Button size="lg" variant="secondary" className="cursor-pointer">
                <span className="flex justify-between">
                  <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                  <span className="pl-1">Create New Ranking</span>
                </span>
              </Button>
            </Link>
          )}
        />

        {draftRankings.length === 0 ? (
          <div className="mb-8 rounded-lg border bg-card">
            <div className="border-b p-6 py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">You haven't imported any rankings yet.</h3>
              <p className="mb-6 text-gray-500 dark:text-gray-400">Import your first ranking to get started</p>
              <Link href={route('rankings.create')}>
                <Button>Create New Ranking</Button>
              </Link>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 w-full">
            <Card>
              <CardHeader>
                <CardTitle>Draft Rankings</CardTitle>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
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
                    {draftRankings.map((draftRank) => (
                      <TableRow key={draftRank.id}>
                        <TableCell>{draftRank.player.full_name}</TableCell>
                        <TableCell>{draftRank.player.position.abbreviation}</TableCell>
                        <TableCell>{draftRank.player.team.abbreviation}</TableCell>
                        <TableCell>{draftRank.average_rank}</TableCell>
                        <TableCell>{draftRank.average_value}</TableCell>
                        <TableCell>{draftRank.fp_ranking}</TableCell>
                        <TableCell>{draftRank.fp_tier}</TableCell>
                        <TableCell>{draftRank.fp_adp}</TableCell>
                        <TableCell>{draftRank.fp_adv}</TableCell>
                        <TableCell>{draftRank.fp_ecr_vs_adp}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </AppLayout>
  );
}
