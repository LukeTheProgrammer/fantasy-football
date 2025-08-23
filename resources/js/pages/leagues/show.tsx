import { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Skeleton } from '@/components/ui/skeleton';
import { useToast } from '@/components/ui/use-toast';
import LeagueMemberManager from '@/components/leagues/LeagueMemberManager';
import axios from '@/lib/axios';

interface LeagueMember {
  id: number;
  league_id: number;
  user_id: number;
  team_name: string;
  team_logo: string | null;
  draft_position: number | null;
  is_admin: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  user: {
    id: number;
    name: string;
    email: string;
  };
}

interface LeagueSettings {
  id: number;
  league_id: number;
  roster_positions: string[];
  roster_size: number;
  starters_count: number;
  bench_count: number;
  ir_spots: number;
  passing_yards_per_point: number;
  passing_td_points: number;
  interception_points: number;
  rushing_yards_per_point: number;
  rushing_td_points: number;
  receiving_yards_per_point: number;
  receiving_td_points: number;
  reception_points: number;
  fumble_lost_points: number;
  two_point_conversion_points: number;
  field_goal_0_39_points: number;
  field_goal_40_49_points: number;
  field_goal_50_plus_points: number;
  extra_point_points: number;
  defense_sack_points: number;
  defense_interception_points: number;
  defense_fumble_recovery_points: number;
  defense_td_points: number;
  defense_safety_points: number;
  defense_points_allowed_tiers: Record<string, number>;
  created_at: string;
  updated_at: string;
}

interface League {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  max_teams: number;
  is_public: boolean;
  draft_type: string;
  draft_date: string | null;
  join_code: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  creator: {
    id: number;
    name: string;
  };
  settings: LeagueSettings;
  members: LeagueMember[];
  user_is_admin: boolean;
  user_is_member: boolean;
}

export default function ShowLeague({ params, auth }: PageProps) {
  const { toast } = useToast();
  const [league, setLeague] = useState<League | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState('overview');
  // We'll manage invite state in the LeagueMemberManager component

  useEffect(() => {
    const fetchLeague = async () => {
      try {
        setLoading(true);
        const response = await axios.get<League>(`/api/leagues/${params.id}`);
        setLeague(response.data);
        setError(null);
      } catch (err) {
        setError('Failed to load league details. Please try again later.');
        console.error('Error fetching league:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchLeague();
  }, [params.id]);

  const handleMembersChange = (updatedMembers: LeagueMember[]) => {
    if (league) {
      setLeague({
        ...league,
        members: updatedMembers
      });
    }
  };

  const copyJoinCode = () => {
    if (league?.join_code) {
      navigator.clipboard.writeText(league.join_code);
      toast({
        title: "Join Code Copied",
        description: "The join code has been copied to your clipboard"
      });
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not scheduled';
    return new Date(dateString).toLocaleString();
  };

  if (loading) {
    return (
      <>
        <Head title="Loading League..." />
        <div className="py-12">
          <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
              <Skeleton className="h-8 w-1/3 mb-6" />
              <Skeleton className="h-4 w-full mb-2" />
              <Skeleton className="h-4 w-5/6 mb-6" />
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Skeleton className="h-32" />
                <Skeleton className="h-32" />
                <Skeleton className="h-32" />
              </div>
            </div>
          </div>
        </div>
      </>
    );
  }

  if (error || !league) {
    return (
      <>
        <Head title="Error" />
        <div className="py-12">
          <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
              <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded-md text-red-600 dark:text-red-400">
                {error || "League not found"}
              </div>
              <div className="mt-4">
                <Link href={route('leagues.index')}>
                  <Button variant="outline">Back to Leagues</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <Head title={league.name} />
      
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6">
              <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <div>
                  <h1 className="text-2xl font-semibold">{league.name}</h1>
                  <p className="text-gray-500 dark:text-gray-400">
                    Created by {league.creator.name} • {league.members.length}/{league.max_teams} teams
                  </p>
                </div>
                <div className="flex mt-4 md:mt-0 space-x-2">
                  {league.user_is_admin && (
                    <Link href={route('leagues.edit', league.id)}>
                      <Button variant="outline">Edit League</Button>
                    </Link>
                  )}
                  <Link href={route('leagues.index')}>
                    <Button variant="ghost">Back to Leagues</Button>
                  </Link>
                </div>
              </div>
              
              <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                <TabsList className="grid w-full grid-cols-3">
                  <TabsTrigger value="overview">Overview</TabsTrigger>
                  <TabsTrigger value="members">Members</TabsTrigger>
                  <TabsTrigger value="settings">Settings</TabsTrigger>
                </TabsList>
                
                <TabsContent value="overview" className="mt-6">
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                      <CardHeader>
                        <CardTitle>League Info</CardTitle>
                      </CardHeader>
                      <CardContent>
                        <dl className="space-y-2">
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd>{league.description || 'No description provided'}</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Type</dt>
                            <dd className="capitalize">{league.draft_type}</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Date</dt>
                            <dd>{formatDate(league.draft_date)}</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Visibility</dt>
                            <dd>{league.is_public ? 'Public' : 'Private'}</dd>
                          </div>
                        </dl>
                      </CardContent>
                    </Card>
                    
                    <Card>
                      <CardHeader>
                        <CardTitle>Join Code</CardTitle>
                        <CardDescription>
                          Share this code with others to join your league
                        </CardDescription>
                      </CardHeader>
                      <CardContent>
                        {league.user_is_admin ? (
                          <div className="flex items-center space-x-2">
                            <code className="relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm">
                              {league.join_code}
                            </code>
                            <Button variant="outline" size="sm" onClick={copyJoinCode}>
                              Copy
                            </Button>
                          </div>
                        ) : (
                          <p className="text-sm text-gray-500 dark:text-gray-400">
                            Only league admins can view the join code
                          </p>
                        )}
                      </CardContent>
                    </Card>
                    
                    <Card>
                      <CardHeader>
                        <CardTitle>Draft Status</CardTitle>
                      </CardHeader>
                      <CardContent>
                        {league.draft_date ? (
                          <div className="space-y-2">
                            <p>
                              {new Date(league.draft_date) > new Date() 
                                ? 'Draft scheduled for:' 
                                : 'Draft was scheduled for:'}
                            </p>
                            <p className="font-medium">{formatDate(league.draft_date)}</p>
                            {new Date(league.draft_date) > new Date() && (
                              <Button className="w-full mt-2">
                                Enter Draft Room
                              </Button>
                            )}
                          </div>
                        ) : (
                          <p className="text-gray-500 dark:text-gray-400">
                            Draft not yet scheduled
                          </p>
                        )}
                      </CardContent>
                    </Card>
                  </div>
                </TabsContent>
                
                <TabsContent value="members" className="mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>League Members</CardTitle>
                      <CardDescription>
                        Manage your league's members and their draft positions
                      </CardDescription>
                    </CardHeader>
                    <CardContent>
                      <LeagueMemberManager
                        leagueId={league.id}
                        members={league.members}
                        maxTeams={league.max_teams}
                        userIsAdmin={league.user_is_admin}
                        currentUserId={auth.user?.id || 0}
                        onMembersChange={handleMembersChange}
                      />
                    </CardContent>
                  </Card>
                </TabsContent>
                
                <TabsContent value="settings" className="mt-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                      <CardHeader>
                        <CardTitle>Roster Settings</CardTitle>
                      </CardHeader>
                      <CardContent>
                        <dl className="space-y-2">
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Roster Size</dt>
                            <dd>{league.settings.roster_size} players</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Starting Players</dt>
                            <dd>{league.settings.starters_count} players</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Bench Spots</dt>
                            <dd>{league.settings.bench_count} players</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">IR Spots</dt>
                            <dd>{league.settings.ir_spots} spots</dd>
                          </div>
                          <div>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Roster Positions</dt>
                            <dd>{league.settings.roster_positions.join(', ')}</dd>
                          </div>
                        </dl>
                      </CardContent>
                    </Card>
                    
                    <Card>
                      <CardHeader>
                        <CardTitle>Scoring Settings</CardTitle>
                      </CardHeader>
                      <CardContent className="h-[300px] overflow-y-auto">
                        <dl className="space-y-2">
                          <div className="pb-2 border-b">
                            <dt className="text-sm font-medium">Passing</dt>
                            <dd className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                              <div className="flex justify-between">
                                <span>Yards per point:</span>
                                <span>{league.settings.passing_yards_per_point}</span>
                              </div>
                              <div className="flex justify-between">
                                <span>TD:</span>
                                <span>{league.settings.passing_td_points} pts</span>
                              </div>
                              <div className="flex justify-between">
                                <span>Interception:</span>
                                <span>{league.settings.interception_points} pts</span>
                              </div>
                            </dd>
                          </div>
                          
                          <div className="pb-2 border-b">
                            <dt className="text-sm font-medium">Rushing</dt>
                            <dd className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                              <div className="flex justify-between">
                                <span>Yards per point:</span>
                                <span>{league.settings.rushing_yards_per_point}</span>
                              </div>
                              <div className="flex justify-between">
                                <span>TD:</span>
                                <span>{league.settings.rushing_td_points} pts</span>
                              </div>
                            </dd>
                          </div>
                          
                          <div className="pb-2 border-b">
                            <dt className="text-sm font-medium">Receiving</dt>
                            <dd className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                              <div className="flex justify-between">
                                <span>Yards per point:</span>
                                <span>{league.settings.receiving_yards_per_point}</span>
                              </div>
                              <div className="flex justify-between">
                                <span>TD:</span>
                                <span>{league.settings.receiving_td_points} pts</span>
                              </div>
                              <div className="flex justify-between">
                                <span>Reception:</span>
                                <span>{league.settings.reception_points} pts</span>
                              </div>
                            </dd>
                          </div>
                          
                          <div className="pb-2 border-b">
                            <dt className="text-sm font-medium">Miscellaneous</dt>
                            <dd className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                              <div className="flex justify-between">
                                <span>Fumble Lost:</span>
                                <span>{league.settings.fumble_lost_points} pts</span>
                              </div>
                              <div className="flex justify-between">
                                <span>2-Point Conversion:</span>
                                <span>{league.settings.two_point_conversion_points} pts</span>
                              </div>
                            </dd>
                          </div>
                        </dl>
                      </CardContent>
                    </Card>
                  </div>
                </TabsContent>
              </Tabs>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
