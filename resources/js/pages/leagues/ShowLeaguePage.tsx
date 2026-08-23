import { AppLayout } from '@/pages/layouts/AppLayout';
import { DraftTab } from '@/modules/leagues/components/tabs/DraftTab';
import { Heading } from '@/common/heading/Heading';
import { RostersTab } from '@/modules/leagues/components/tabs/RostersTab';
import { SeasonSelect } from '@/modules/leagues/components/SeasonSelect';
import { SettingsTab } from '@/modules/leagues/components/tabs/SettingsTab';
import { StandingsTab } from '@/modules/leagues/components/tabs/StandingsTab';
import { MatchupsTab } from '@/modules/leagues/components/tabs/MatchupsTab';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { type BreadcrumbItem } from '@/types';
import { type LeagueResource , type LeagueMemberResource } from '@/types/resources';
import { type SeasonOption } from '@/types/models';
import { useState, useEffect } from 'react';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

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

interface LeagueShowProps extends PageProps {
  league: LeagueResource;
  seasons: SeasonOption[];
}

export default function ShowLeague({ league, seasons }: LeagueShowProps) {
  const [selectedMemberId, setSelectedMemberId] = useState<string>('');
  const [selectedMember, setSelectedMember] = useState<LeagueMemberResource | null>(null);
  const [selectedWeek, setSelectedWeek] = useState<string>(league.week ? `Week ${league.week}` : 'Week 1');

  // Select the first member by default when the component mounts
  useEffect(() => {
    if (league.members.length > 0 && !selectedMemberId) {
      setSelectedMemberId(league.members[0].id.toString());
    }
  }, [league.members, selectedMemberId]);

  useEffect(() => {
    if (league.members.length > 0 && selectedMemberId) {
      const lm = league.members.find(m => m.id.toString() === selectedMemberId) || league.members[0];
      setSelectedMember(lm);
    }
  }, [league.members, selectedMemberId]);

  useEffect(() => {
    if (!selectedWeek) {
      setSelectedWeek(league.week ? `Week ${league.week}` : 'Week 1');
    }
  }, [selectedWeek, league.week]);

  const handleMemberIdChange = (memberId: string) => {
    setSelectedMemberId(memberId);
  };

  const handleWeekChange = (week: string) => {
    setSelectedWeek(week);
  };

  const getWeeks = (): string[] => {
    return Object.keys(league.matchups).map(m => `Week ${m}`);
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={league.name} />

      <div className="flex-1 p-8">
        <div className="mb-2 flex flex-col items-start justify-between md:flex-row">
          <div>
            <Heading
              title={league.name}
              description={`${league.platform} • ${league.team_count} teams`}
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
          <Tabs defaultValue="rosters">
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center">
                <TabsList>
                  <TabsTrigger className="w-[7rem]" value="rosters">Rosters</TabsTrigger>
                  <TabsTrigger className="w-[7rem]" value="matchups">Matchups</TabsTrigger>
                  <TabsTrigger className="w-[7rem]" value="standings">Standings</TabsTrigger>
                  <TabsTrigger className="w-[7rem]" value="settings">Settings</TabsTrigger>
                  <TabsTrigger className="w-[7rem]" value="draft">Draft</TabsTrigger>
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
              <RostersTab
                league={league}
                selectedMember={selectedMember}
                selectedWeek={selectedWeek}
              />
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
