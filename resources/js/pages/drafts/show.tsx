import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface Draft {
  id: number;
  league_id: number;
  draft_date: string;
  draft_type: string;
  is_completed: boolean;
  auction_budget: number;
  current_pick: number;
  current_round: number;
  time_per_pick: number;
  is_active: boolean;
  league: {
    name: string;
    year: number;
    members: {
      user_id: number;
      is_admin: boolean;
      team_name: string;
    }[];
  };
}

interface DraftShowProps extends PageProps {
  draft: Draft;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Drafts',
    href: '/drafts',
  },
  {
    title: 'Draft Details',
    href: '#',
  },
];

function getUserMember(draft: Draft, userId: number) {
  return draft.league.members.find((member) => member.user_id === userId);
}

function isUserAdmin(draft: Draft, userId: number) {
  return getUserMember(draft, userId)?.is_admin;
}

export default function ShowDraft({ draft }: DraftShowProps) {
  const { auth } = usePage<SharedData>().props;
  const userId = auth.user.id;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${draft.league.name} ${draft.league.year}`} />

      <div className="flex-1 p-8">
        <div className="mb-6 flex flex-col items-start justify-between md:flex-row md:items-center">
          <div>
            <Heading
              title={`${draft.league.name} ${draft.league.year}`}
            />
          </div>
          <div className="mt-4 flex space-x-2 md:mt-0">
            {isUserAdmin(draft, userId) && (
              <Link href={route('drafts.edit', draft.id)}>
                <Button variant="outline">Edit Draft</Button>
              </Link>
            )}
            <Link href={route('drafts.index')}>
              <Button variant="outline">Back to Drafts</Button>
            </Link>
          </div>
        </div>

        <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
          {/* League Details Card */}
          <Card>
            <CardContent className="space-y-4">
              <div className="mb-8 grid w-full">
                <h2 className="text-lg font-semibold">Draft Info</h2>
                <p className="text-sm text-muted-foreground">Basic information about your fantasy football draft.</p>
              </div>

            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
