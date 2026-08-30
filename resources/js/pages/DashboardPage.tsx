import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem } from '@/types';
import type { LeagueResource } from '@/types/resources';
import { Head, Link } from '@inertiajs/react';
import { Play, Plus, Search, Star, Trophy } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

interface DashboardProps {
  leagues: LeagueResource[];
}

export default function Dashboard({ leagues }: DashboardProps) {
  function draftDate(date: string | null | undefined): string {
    if (!date) {
      return '';
    }

    return new Date(date).toLocaleDateString();
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Dashboard" />

      <div className="flex-1 p-8">
        <Heading
          title="My Fantasy Leagues"
          description="Manage and track your fantasy football leagues"
          rightContent={
            <Link href="/leagues/create">
              <Button size="lg" variant="secondary" className="cursor-pointer">
                <span className="flex justify-between">
                  <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                  <span className="pl-1">Create New League</span>
                </span>
              </Button>
            </Link>
          }
        />

        <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
          <Card>
            <CardContent>
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm">Total Leagues</p>
                  <p className="text-2xl">4</p>
                </div>
                <div className="rounded-lg p-3">
                  <Trophy className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent>
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm">Active Drafts</p>
                  <p className="text-2xl">2</p>
                </div>
                <div className="rounded-lg p-3">
                  <Play className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent>
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm">Wins This Season</p>
                  <p className="text-2xl">12</p>
                </div>
                <div className="rounded-lg p-3">
                  <Star className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card className="pt-4">
          <CardHeader>
            <div className="flex w-full items-center justify-between">
              <CardTitle>Your Leagues</CardTitle>
              <div className="flex justify-end gap-4">
                <div className="flex items-center space-x-4">
                  <div className="relative">
                    <Input type="text" placeholder="Search leagues..." className="rounded-full border py-2 pr-4 pl-10 text-sm focus:ring-2" />
                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform" />
                  </div>
                  <Select>
                    <SelectTrigger className="w-45">
                      <SelectValue placeholder="Filter Leagues" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Leagues</SelectItem>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="completed">Completed</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="divide-y">
              {leagues.map((league) => (
                <div key={league.id} className="p-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className="flex h-12 w-12 items-center justify-center rounded-lg">
                        <Trophy className="h-5 w-5" />
                      </div>
                      <div>
                        <h3 className="">{league.name}</h3>
                        <p className="text-sm">
                          {league.team_count} teams • {league.draft?.draft_type}
                        </p>
                        <p className="text-sm">Draft: {draftDate(league.draft?.draft_date)}</p>
                      </div>
                    </div>
                    <div className="flex items-center space-x-4">
                      <div className="flex items-center justify-end space-x-2">
                        {league?.draft && league?.draft?.is_completed === false && (
                          <Link href={route('drafts.draft-room', league.draft.id)}>
                            <Button variant="outline" className="text-right">
                              Draft Room
                            </Button>
                          </Link>
                        )}
                        {league?.draft && (
                          <Link href={route('drafts.show', [league.draft.id, league.season_id])}>
                            <Button variant="outline">Draft</Button>
                          </Link>
                        )}
                        <Link href={route('leagues.show', league.id)}>
                          <Button variant="outline">League</Button>
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
