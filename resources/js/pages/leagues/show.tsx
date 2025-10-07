import AppLayout from '@/layouts/app-layout';
import DraftTab from '@/components/leagues/tab-content/draft-tab';
import Heading from '@/components/heading';
import RostersTab from '@/components/leagues/tab-content/rosters-tab';
import SettingsTab from '@/components/leagues/tab-content/settings-tab';
import StandingsTab from '@/components/leagues/tab-content/standings-tab';
import MatchupsTab from '@/components/leagues/tab-content/matchups-tab';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { type BreadcrumbItem } from '@/types';
import { type LeagueResource , type LeagueMemberResource } from '@/types/resources';
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
}

export default function ShowLeague({ league }: LeagueShowProps) {
  const [selectedMemberId, setSelectedMemberId] = useState<string>('');
  const [selectedMember, setSelectedMember] = useState<LeagueMemberResource | null>(null);
  const [selectedWeek, setSelectedWeek] = useState<string>('Week 1');

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
      setSelectedWeek('Week 1');
    }
  }, [selectedWeek]);

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
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row">
          <div>
            <Heading
              title={league.name}
              description={`${league.platform} • ${league.team_count} teams`}
            />
          </div>
          <div className="mt-4 flex space-x-2 md:mt-0">
            {league.is_admin && (
              <Link href={route('leagues.edit', league.id)}>
                <Button variant="outline">Edit League</Button>
              </Link>
            )}
            <Link href={route('leagues.index')}>
              <Button variant="outline">Back to Leagues</Button>
            </Link>
          </div>
        </div>

        <div className="mb-8">
          <Tabs defaultValue="rosters">
            <div className="flex items-center justify-between mb-6">
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
                  <SelectTrigger className="w-[16em]">
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
