import { Heading } from '@/common/heading/Heading';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { SeasonSelect } from '@/modules/leagues/components/SeasonSelect';
import { DraftTab } from '@/modules/leagues/components/tabs/DraftTab';
import { MatchupsTab } from '@/modules/leagues/components/tabs/MatchupsTab';
import { RostersTab } from '@/modules/leagues/components/tabs/RostersTab';
import { SettingsTab } from '@/modules/leagues/components/tabs/SettingsTab';
import { StandingsTab } from '@/modules/leagues/components/tabs/StandingsTab';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { PageProps, type BreadcrumbItem } from '@/types';
import { type SeasonOption } from '@/types/models';
import { type LeagueMemberResource, type LeagueResource } from '@/types/resources';
import { Head, Link, router } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import { useEffect, useState } from 'react';

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

interface LeagueSyncedPayload {
  status: string;
  message: string | null;
  synced_at: string;
}

interface LeagueShowProps extends PageProps {
  league: LeagueResource;
  seasons: SeasonOption[];
}

export default function ShowLeague({ league, seasons }: LeagueShowProps) {
  const [selectedMemberId, setSelectedMemberId] = useState<string>('');
  const [selectedMember, setSelectedMember] = useState<LeagueMemberResource | null>(null);
  const [selectedWeek, setSelectedWeek] = useState<string>(league.week ? `Week ${league.week}` : 'Week 1');
  // Seeded from the server so a refresh mid sync still shows the button
  // waiting, rather than offering a second sync of the same league.
  const [syncing, setSyncing] = useState<boolean>(league.is_syncing);
  const [syncError, setSyncError] = useState<string | null>(null);

  // Select the first member by default when the component mounts
  useEffect(() => {
    if (league.members.length > 0 && !selectedMemberId) {
      setSelectedMemberId(league.members[0].id.toString());
    }
  }, [league.members, selectedMemberId]);

  useEffect(() => {
    if (league.members.length > 0 && selectedMemberId) {
      const lm = league.members.find((m) => m.id.toString() === selectedMemberId) || league.members[0];
      setSelectedMember(lm);
    }
  }, [league.members, selectedMemberId]);

  useEffect(() => {
    if (!selectedWeek) {
      setSelectedWeek(league.week ? `Week ${league.week}` : 'Week 1');
    }
  }, [selectedWeek, league.week]);

  // The sync runs on the queue, so the button stays waiting until the job
  // says it is done rather than until the request comes back.
  const syncLeague = () => {
    router.post(
      route('leagues.sync.store', league.id),
      {},
      {
        preserveScroll: true,
        preserveState: true,
        onStart: () => setSyncing(true),
        onError: () => setSyncing(false),
      },
    );
  };

  useEcho<LeagueSyncedPayload>(
    `league.${league.id}`,
    '.LeagueSynced',
    (payload) => {
      setSyncing(false);
      setSyncError(payload.status === 'failed' ? (payload.message ?? 'Sync failed.') : null);

      // The page is built server side, so the new data is fetched rather than
      // patched in here.
      if (payload.status !== 'failed') {
        router.reload({ only: ['league'] });
      }
    },
    [league.id],
  );

  const handleMemberIdChange = (memberId: string) => {
    setSelectedMemberId(memberId);
  };

  const handleWeekChange = (week: string) => {
    setSelectedWeek(week);
  };

  const getWeeks = (): string[] => {
    return Object.keys(league.matchups).map((m) => `Week ${m}`);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={league.name} />

      <div className="flex-1 p-8">
        <div className="mb-2 flex w-full flex-col items-start justify-between md:flex-row">
          <div className="w-full">
            <Heading
              title={league.name}
              description={`${league.platform} • ${league.team_count} teams`}
              rightContent={
                <div className="flex items-center gap-2">
                  {syncError && <span className="text-xs text-destructive">{syncError}</span>}
                  <Button onClick={syncLeague} disabled={syncing}>
                    {syncing ? 'Syncing…' : 'Sync League'}
                  </Button>
                </div>
              }
            />
          </div>
          <div className="mt-4 flex items-center space-x-2 md:mt-0">
            <SeasonSelect seasons={seasons} season={league.season} routeName="leagues.show" />
            {league.is_admin && (
              <Link href={route('leagues.edit', league.id)}>
                <Button variant="outline">Edit League</Button>
              </Link>
            )}
          </div>
        </div>

        <div className="mb-2">
          <Tabs defaultValue="standings">
            <div className="mb-2 flex items-center justify-between">
              <div className="flex items-center">
                <TabsList>
                  <TabsTrigger className="w-28" value="standings">
                    Standings
                  </TabsTrigger>
                  <TabsTrigger className="w-28" value="rosters">
                    Rosters
                  </TabsTrigger>
                  <TabsTrigger className="w-28" value="matchups">
                    Matchups
                  </TabsTrigger>
                  <TabsTrigger className="w-28" value="settings">
                    Settings
                  </TabsTrigger>
                  <TabsTrigger className="w-28" value="draft">
                    Draft
                  </TabsTrigger>
                </TabsList>
              </div>
              <div className="flex items-center justify-end space-x-2">
                <Select value={selectedMemberId} onValueChange={(value) => handleMemberIdChange(value)}>
                  <SelectTrigger className="w-[20em]">
                    <SelectValue placeholder={`Week ${selectedWeek}`} />
                  </SelectTrigger>
                  <SelectContent>
                    {league.members.map((member) => (
                      <SelectItem key={member.id} value={member.id.toString()}>
                        {member.team_name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>

                <Select value={selectedWeek} onValueChange={(value) => handleWeekChange(value)}>
                  <SelectTrigger className="w-[16em]">
                    <SelectValue placeholder={`Week ${selectedWeek}`} />
                  </SelectTrigger>
                  <SelectContent>
                    {getWeeks().map((week) => (
                      <SelectItem key={week} value={week}>
                        {week}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
            <TabsContent value="rosters">
              <RostersTab league={league} selectedMember={selectedMember} selectedWeek={selectedWeek} />
            </TabsContent>
            <TabsContent value="matchups">
              <MatchupsTab league={league} selectedMember={selectedMember} />
            </TabsContent>
            <TabsContent value="standings">
              <StandingsTab league={league} />
            </TabsContent>
            <TabsContent value="settings">
              <SettingsTab league={league} />
            </TabsContent>
            <TabsContent value="draft">
              <DraftTab league={league} />
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </AppLayout>
  );
}
