import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import axios from '@/lib/axios';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Skeleton } from '@/components/ui/skeleton';
import { type BreadcrumbItem } from '@/types';
import { useEffect, useState } from 'react';

interface League {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  max_teams: number;
  is_public: boolean;
  draft_type: string;
  draft_date: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  pivot: {
    team_name: string;
    is_admin: boolean;
  };
  _count?: {
    members: number;
  };
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Leagues',
    href: '/leagues',
  },
];

export default function Leagues() {
  const [leagues, setLeagues] = useState<League[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchLeagues = async () => {
      try {
        setLoading(true);
        // Get CSRF cookie first
        // await getCsrfToken();
        // Then make the API request
        const response = await axios.get<League[]>('/api/leagues');
        setLeagues(response.data);
        setError(null);
      } catch (err) {
        // Check if this is an authentication error
        const error = err as { response?: { status: number; data?: { message?: string } } };
        if (error.response && error.response.status === 401) {
          setError('You need to be logged in to view your leagues.');
          // Login link is already provided in the UI
        } else {
          const errorMessage = error.response?.data?.message || 'Failed to load leagues. Please try again later.';
          setError(errorMessage);
        }
        console.error('Error fetching leagues:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchLeagues();
  }, []);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="My Leagues" />

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

        {loading ? (
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {[...Array(3)].map((_, i) => (
              <Card key={i} className="overflow-hidden">
                <CardHeader className="pb-3">
                  <Skeleton className="mb-2 h-6 w-3/4" />
                  <Skeleton className="h-4 w-1/2" />
                </CardHeader>
                <CardContent>
                  <Skeleton className="mb-2 h-4 w-full" />
                  <Skeleton className="h-4 w-5/6" />
                </CardContent>
                <CardFooter>
                  <Skeleton className="h-9 w-full" />
                </CardFooter>
              </Card>
            ))}
          </div>
        ) : leagues.length === 0 ? (
          <div className="mb-8 rounded-lg border bg-card">
            <div className="border-b p-6 py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">You haven't joined any leagues yet</h3>
              <p className="mb-6 text-gray-500 dark:text-gray-400">Create your first league to get started</p>
              <Link href={route('leagues.create')}>
                <Button>Create New League</Button>
              </Link>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {leagues.map((league) => (
              <Card key={league.id} className="overflow-hidden">
                <CardHeader>
                  <CardTitle>{league.name}</CardTitle>
                  <CardDescription>
                    {league.pivot.is_admin && (
                      <Badge variant="outline" className="mr-2">
                        Admin
                      </Badge>
                    )}
                    {league.draft_type === 'snake' ? 'Snake Draft' : 'Auction Draft'}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="mb-2 text-sm text-gray-500 dark:text-gray-400">{league.description || 'No description provided'}</p>
                  <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <span>Your team: {league.pivot.team_name}</span>
                    <span className="mx-2">•</span>
                    <span>
                      {league._count?.members || 'N/A'} / {league.max_teams} teams
                    </span>
                  </div>
                </CardContent>
                <CardFooter>
                  <Link href={route('leagues.show', league.id)} className="w-full">
                    <Button variant="outline" className="w-full">
                      View League
                    </Button>
                  </Link>
                </CardFooter>
              </Card>
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}
