import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import LeagueMemberManager from '@/components/form/member-manager';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { toast } from 'sonner';
import { useEffect, useState } from 'react';
import { type BreadcrumbItem } from '@/types';
import DraftsTable from '@/components/leagues/drafts-table';

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
  team_count: number;
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

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Leagues',
    href: '/leagues',
  },
  {
    title: 'League Details',
    href: '#',
  },
];

export default function ShowLeague({ league: initialLeague, auth }: PageProps & { league: League }) {
  const [league, setLeague] = useState<League | null>(initialLeague || null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  // We don't need activeTab state as we're using defaultValue in Tabs
  // We'll manage invite state in the LeagueMemberManager component

  useEffect(() => {
    if (initialLeague) {
      // Set user_is_admin based on membership status
      const userIsAdmin = initialLeague.members.some(
        (member) => member.user_id === auth.user?.id && member.is_admin
      );

      setLeague({
        ...initialLeague,
        user_is_admin: userIsAdmin
      });

      setLoading(false);
      return;
    }
  }, [initialLeague, auth.user?.id]);

  const handleMembersChange = (updatedMembers: LeagueMember[]) => {
    if (league) {
      setLeague({
        ...league,
        members: updatedMembers,
      });
    }
  };

  const copyJoinCode = () => {
    if (league?.join_code) {
      navigator.clipboard.writeText(league.join_code);
      toast.success('The join code has been copied to your clipboard');
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not scheduled';
    return new Date(dateString).toLocaleString();
  };

  if (loading) {
    return (
      <AppLayout breadcrumbs={breadcrumbs}>
        <Head title="Loading League..." />
        <div className="py-12">
          <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
              <Skeleton className="mb-6 h-8 w-1/3" />
              <Skeleton className="mb-2 h-4 w-full" />
              <Skeleton className="mb-6 h-4 w-5/6" />
              <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Skeleton className="h-32" />
                <Skeleton className="h-32" />
                <Skeleton className="h-32" />
              </div>
            </div>
          </div>
        </div>
      </AppLayout>
    );
  }

  if (error || !league) {
    return (
      <AppLayout breadcrumbs={breadcrumbs}>
        <Head title="Error" />
        <div className="py-12">
          <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
              <div className="rounded-md border border-red-200 bg-red-50 p-4 text-red-600 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                {error || 'League not found'}
              </div>
              <div className="mt-4">
                <Link href={route('leagues.index')}>
                  <Button variant="outline">Back to Leagues</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={league?.name} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <div>
            <Heading
              title={league?.name}
              description={`Created by ${league.creator?.name} • ${league.members.length}/${league.team_count} teams`}
            />
          </div>
          <div className="mt-4 flex space-x-2 md:mt-0">
            {league.user_is_admin && (
              <Link href={route('leagues.edit', league.id)}>
                <Button variant="outline">Edit League</Button>
              </Link>
            )}
            <Link href={route('leagues.index')}>
              <Button variant="outline">Back to Leagues</Button>
            </Link>
          </div>
        </div>

        <div className="mb-8 rounded-lg border bg-card">
          <div className="border-b p-6">
            <h2 className="text-lg font-semibold">Drafts</h2>
            <DraftsTable leagueId={league.id} />
          </div>
        </div>

        {/* League Members Card */}
        <div className="mb-8">
          <Card>
            <CardContent>
              <LeagueMemberManager
                leagueId={league.id}
                members={league.members}
                maxTeams={league.team_count}
                userIsAdmin={league.user_is_admin}
                currentUserId={auth.user?.id || 0}
                onMembersChange={handleMembersChange}
              />
            </CardContent>
          </Card>
        </div>

        <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
          {/* League Details Card */}
          <Card>
            <CardContent className="space-y-4">
              <div className="mb-8 grid w-full">
                <h2 className="text-lg font-semibold">League Info</h2>
                <p className="text-sm text-muted-foreground">Basic information about your fantasy football league.</p>
              </div>

              <dl className="space-y-4">
                <div>
                  <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                  <dd className="mt-1">{league.description || 'No description provided'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Type</dt>
                  <dd className="mt-1 capitalize">{league.draft_type}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Date</dt>
                  <dd className="mt-1">{formatDate(league.draft_date)}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Visibility</dt>
                  <dd className="mt-1">{league.is_public ? 'Public' : 'Private'}</dd>
                </div>
              </dl>

              <div className="mt-6">
                <h3 className="mb-2 text-md font-medium">Join Code</h3>
                {league.user_is_admin ? (
                  <div className="flex items-center space-x-2">
                    <code className="relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm">{league.join_code}</code>
                    <Button variant="outline" size="sm" onClick={copyJoinCode}>
                      Copy
                    </Button>
                  </div>
                ) : (
                  <p className="text-sm text-gray-500 dark:text-gray-400">Only league admins can view the join code</p>
                )}
              </div>

              <div className="mt-6">
                <h3 className="mb-2 text-md font-medium">Draft Status</h3>
                {league.draft_date ? (
                  <div className="space-y-2">
                    <p>{new Date(league.draft_date) > new Date() ? 'Draft scheduled for:' : 'Draft was scheduled for:'}</p>
                    <p className="font-medium">{formatDate(league.draft_date)}</p>
                    {new Date(league.draft_date) > new Date() && <Button className="mt-2 w-full">Enter Draft Room</Button>}
                  </div>
                ) : (
                  <p className="text-gray-500 dark:text-gray-400">Draft not yet scheduled</p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Roster Settings Card */}
          <Card>
            <CardContent className="space-y-4">
              <div className="mb-8 grid w-full">
                <h2 className="text-lg font-semibold">Roster Settings</h2>
                <p className="text-sm text-muted-foreground">Your league's roster positions and size.</p>
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                  <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Starters</h3>
                  <p className="mt-1 text-lg font-semibold">{league.settings.starters_count}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Bench</h3>
                  <p className="mt-1 text-lg font-semibold">{league.settings.bench_count}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">IR</h3>
                  <p className="mt-1 text-lg font-semibold">{league.settings.ir_spots}</p>
                </div>
              </div>

              <div className="mt-6">
                <h3 className="mb-2 text-md font-medium">Roster Positions</h3>
                <div className="flex flex-wrap gap-2">
                  {league.settings.roster_positions.map((position, index) => (
                    <div key={index} className="rounded-md bg-gray-100 px-2 py-1 text-sm dark:bg-gray-700">
                      {position}
                    </div>
                  ))}
                </div>
              </div>

              <div className="mt-6">
                <h3 className="mb-2 text-md font-medium">Total Roster Size</h3>
                <p className="text-lg font-semibold">{league.settings.roster_size} players</p>
              </div>
            </CardContent>
          </Card>

          {/* Scoring Settings Card */}
          <Card>
            <CardContent className="space-y-4">
              <div className="mb-8 grid w-full">
                <h2 className="text-lg font-semibold">Scoring Settings</h2>
                <p className="text-sm text-muted-foreground">Your league's scoring rules.</p>
              </div>

              <div>
                <h3 className="text-lg font-medium">Passing</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
                  <div className="text-right">{league.settings.passing_yards_per_point}</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">TD Pass</div>
                  <div className="text-right">{league.settings.passing_td_points} pts</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">Interception</div>
                  <div className="text-right">{league.settings.interception_points} pts</div>
                </div>
              </div>

              <div className="mt-4">
                <h3 className="text-lg font-medium">Rushing</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
                  <div className="text-right">{league.settings.rushing_yards_per_point}</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">TD</div>
                  <div className="text-right">{league.settings.rushing_td_points} pts</div>
                </div>
              </div>

              <div className="mt-4">
                <h3 className="text-lg font-medium">Receiving</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
                  <div className="text-right">{league.settings.receiving_yards_per_point}</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">TD</div>
                  <div className="text-right">{league.settings.receiving_td_points} pts</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">Reception</div>
                  <div className="text-right">{league.settings.reception_points} pts</div>
                </div>
              </div>

              <div className="mt-4">
                <h3 className="text-lg font-medium">Miscellaneous</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="text-sm text-gray-500 dark:text-gray-400">Fumble Lost</div>
                  <div className="text-right">{league.settings.fumble_lost_points} pts</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">2-Point Conversion</div>
                  <div className="text-right">{league.settings.two_point_conversion_points} pts</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

      </div>
    </AppLayout>
  );
}
