import { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import axios from 'axios';

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

export default function Leagues({ auth }: PageProps) {
  const [leagues, setLeagues] = useState<League[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchLeagues = async () => {
      try {
        setLoading(true);
        const response = await axios.get<League[]>('/api/leagues');
        setLeagues(response.data);
        setError(null);
      } catch (err) {
        setError('Failed to load leagues. Please try again later.');
        console.error('Error fetching leagues:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchLeagues();
  }, []);

  return (
    <>
      <Head title="My Leagues" />
      
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6">
              <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-semibold">My Fantasy Leagues</h1>
                <Link href={route('leagues.create')}>
                  <Button>Create New League</Button>
                </Link>
              </div>
              
              {loading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {[...Array(3)].map((_, i) => (
                    <Card key={i} className="overflow-hidden">
                      <CardHeader className="pb-3">
                        <Skeleton className="h-6 w-3/4 mb-2" />
                        <Skeleton className="h-4 w-1/2" />
                      </CardHeader>
                      <CardContent>
                        <Skeleton className="h-4 w-full mb-2" />
                        <Skeleton className="h-4 w-5/6" />
                      </CardContent>
                      <CardFooter>
                        <Skeleton className="h-9 w-full" />
                      </CardFooter>
                    </Card>
                  ))}
                </div>
              ) : error ? (
                <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded-md text-red-600 dark:text-red-400">
                  {error}
                </div>
              ) : leagues.length === 0 ? (
                <div className="text-center py-12">
                  <h3 className="text-lg font-medium mb-2">You haven't joined any leagues yet</h3>
                  <p className="text-gray-500 dark:text-gray-400 mb-6">Create your first league to get started</p>
                  <Link href={route('leagues.create')}>
                    <Button>Create New League</Button>
                  </Link>
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {leagues.map(league => (
                    <Card key={league.id} className="overflow-hidden">
                      <CardHeader>
                        <CardTitle>{league.name}</CardTitle>
                        <CardDescription>
                          {league.pivot.is_admin && (
                            <Badge variant="outline" className="mr-2">Admin</Badge>
                          )}
                          {league.draft_type === 'snake' ? 'Snake Draft' : 'Auction Draft'}
                        </CardDescription>
                      </CardHeader>
                      <CardContent>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mb-2">
                          {league.description || 'No description provided'}
                        </p>
                        <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                          <span>Your team: {league.pivot.team_name}</span>
                          <span className="mx-2">•</span>
                          <span>{league._count?.members || 'N/A'} / {league.max_teams} teams</span>
                        </div>
                      </CardContent>
                      <CardFooter>
                        <Link href={route('leagues.show', league.id)} className="w-full">
                          <Button variant="outline" className="w-full">View League</Button>
                        </Link>
                      </CardFooter>
                    </Card>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
