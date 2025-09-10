import AppLayout from '@/layouts/app-layout';
import DraftsTable from '@/components/leagues/drafts-table';
import Heading from '@/components/heading';
import LeagueMemberManager from '@/components/form/member-manager';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Separator } from '@/components/ui/separator';
import { isUserLeagueAdmin } from '@/lib/utils';
import { toast } from 'sonner';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type League, type LeagueMember } from '@/types/models';
import { useState } from 'react';

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

export default function ShowLeague({ league: initialLeague }: PageProps & { league: League }) {
  const { auth } = usePage<SharedData>().props;
  const [league, setLeague] = useState<League>(initialLeague);

  const userId = auth.user.id;
  const userIsAdmin = isUserLeagueAdmin(league, userId);

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

  const playersDrafted = league.draft?.picks.filter(p => p.player_id !== null).length || 0;
  const totalPlayers = league.draft?.picks.length || 0;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={league.name} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <div>
            <Heading
              title={league.name}
              description={`Created by ${league.creator?.name} • ${league.members.length}/${league.team_count} teams`}
            />
          </div>
          <div className="mt-4 flex space-x-2 md:mt-0">
            {userIsAdmin && (
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
          <div className="border-b p-6 grid grid-cols-3">
            <div className="text-left">
              <h2 className="text-lg font-semibold">Draft</h2>
              <p>{league.name} {league.year} Draft</p>
            </div>
            <div className="flex items-center justify-center">
              {playersDrafted > 0 && totalPlayers > 0 ? (
                <p>{playersDrafted} / {totalPlayers} Players Drafted</p>
              ) : (
                <span></span>
              )}
            </div>
            <div className="flex items-center justify-end">
              {league?.draft?.id && (
                <Link href={route('drafts.draft-room', league.draft.id)}>
                  <Button variant="outline" className="text-right">
                    Enter Draft Room
                  </Button>
                </Link>
              )}
            </div>
          </div>
        </div>

        {/* League Members Card */}
        <div className="mb-8">
          <Card>
            <CardContent>
              <LeagueMemberManager
                members={league.members}
                maxTeams={league.team_count}
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
                  <dd className="mt-1 capitalize">{league.draft?.draft_type}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Date</dt>
                  <dd className="mt-1">{formatDate(league.draft?.draft_date)}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Visibility</dt>
                  <dd className="mt-1">{league.is_public ? 'Public' : 'Private'}</dd>
                </div>
              </dl>

              <div className="mt-6">
                <h3 className="mb-2 text-md font-medium">Join Code</h3>
                {userIsAdmin ? (
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
                {league?.draft?.draft_date ? (
                  <div className="space-y-2">
                    <p>{new Date(league.draft.draft_date) > new Date() ? 'Draft scheduled for:' : 'Draft was scheduled for:'}</p>
                    <p className="font-medium">{formatDate(league.draft.draft_date)}</p>
                    {new Date(league.draft.draft_date) > new Date() && <Button className="mt-2 w-full">Enter Draft Room</Button>}
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
                  <div className="text-right">{league.settings.passing_points_per_yard}</div>
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
                  <div className="text-right">{league.settings.rushing_points_per_yard}</div>
                  <div className="text-sm text-gray-500 dark:text-gray-400">TD</div>
                  <div className="text-right">{league.settings.rushing_td_points} pts</div>
                </div>
              </div>

              <div className="mt-4">
                <h3 className="text-lg font-medium">Receiving</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
                  <div className="text-right">{league.settings.receiving_points_per_yard}</div>
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
