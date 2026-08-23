import { AppLayout } from '@/pages/layouts/AppLayout';
import { Heading } from '@/common/heading/Heading';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Play, Plus, Search, Star, Trophy } from 'lucide-react';
import { Head, Link } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { useEffect, useState } from 'react';
import { type League } from '@/types/models';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

export default function Dashboard() {
  const [leagues, setLeagues] = useState<League[]>([]);

  function fetchLeagues() {
    axios
      .get('/api/leagues')
      .then((response) => {
        setLeagues(response.data);
      })
      .catch((error) => {
        console.error(error);
      });
  }

  useEffect(() => {
    fetchLeagues();
  }, []);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Dashboard" />

      <div className="flex-1 p-8">
        <Heading
          title="My Fantasy Leagues"
          description="Manage and track your fantasy football leagues"
          rightContent={(
            <Link href="/leagues/create">
              <Button size="lg" variant="secondary" className="cursor-pointer">
                <span className="flex justify-between">
                  <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                  <span className="pl-1">Create New League</span>
                </span>
              </Button>
            </Link>
          )}
        />

        <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
          <div className="rounded-lg border bg-card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm">Total Leagues</p>
                <p className="text-2xl">4</p>
              </div>
              <div className="rounded-lg p-3">
                <Trophy className="h-5 w-5" />
              </div>
            </div>
          </div>
          <div className="rounded-lg border bg-card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm">Active Drafts</p>
                <p className="text-2xl">2</p>
              </div>
              <div className="rounded-lg p-3">
                <Play className="h-5 w-5" />
              </div>
            </div>
          </div>
          <div className="rounded-lg border bg-card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm">Wins This Season</p>
                <p className="text-2xl">12</p>
              </div>
              <div className="rounded-lg p-3">
                <Star className="h-5 w-5" />
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-lg border bg-card">
          <div className="border-b p-6">
            <div className="flex items-center justify-between">
              <h2 className="text-xl">Your Leagues</h2>
              <div className="flex items-center space-x-4">
                <div className="relative">
                  <Input type="text" placeholder="Search leagues..." className="rounded-full border py-2 pr-4 pl-10 text-sm focus:ring-2" />
                  <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform" />
                </div>
                <Select>
                  <SelectTrigger className="w-[180px]">
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

          <div className="divide-y">
            {leagues.map((league: League) => (
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
                      <p className="text-sm">Draft: {league.draft?.draft_date}</p>
                    </div>
                  </div>
                  <div className="flex items-center space-x-4">
                    <div className="flex items-center justify-end space-x-2">
                      <Link href={`/leagues/${league.id}`}>
                        <Button variant="outline">View</Button>
                      </Link>
                      <Link href={`/leagues/${league.id}/edit`}>
                        <Button variant="outline">Edit</Button>
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
