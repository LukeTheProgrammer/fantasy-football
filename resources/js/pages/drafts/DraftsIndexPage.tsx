import { Heading } from '@/common/heading/Heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { getDraftUserMember } from '@/modules/drafts/helpers/getDraftUserMember';
import { isUserDraftAdmin } from '@/modules/drafts/helpers/isUserDraftAdmin';
import { AppLayout } from '@/pages/layouts/AppLayout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft } from '@/types/models';
import { PageProps } from '@inertiajs/core';
import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Drafts',
    href: '/drafts',
  },
];

interface DraftIndexProps extends PageProps {
  drafts: Draft[];
}

export default function Drafts({ drafts }: DraftIndexProps) {
  const { auth } = usePage<SharedData>().props;
  const userId = auth.user.id;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="My Drafts" />

      <div className="flex-1 p-8">
        <Heading
          title="My Fantasy Drafts"
          description="Manage and track your fantasy football drafts"
          rightContent={
            <Link href="/drafts/create">
              <Button size="lg" variant="secondary" className="cursor-pointer">
                <span className="flex justify-between">
                  <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                  <span className="pl-1">Create New Draft</span>
                </span>
              </Button>
            </Link>
          }
        />

        {drafts.length === 0 ? (
          <div className="mb-8 rounded-lg border bg-card">
            <div className="border-b p-6 py-12 text-center">
              <h3 className="mb-2 text-lg font-medium">You haven't created any drafts yet</h3>
              <p className="mb-6 text-gray-500 dark:text-gray-400">Create your first draft to get started</p>
              <Link href={route('drafts.create')}>
                <Button>Create New Draft</Button>
              </Link>
            </div>
          </div>
        ) : (
          <div>
            {drafts.map((draft) => (
              <Card key={draft.id} className="mb-8 overflow-hidden">
                <CardContent className="grid grid-cols-3 gap-6">
                  <div>
                    {draft.league.name} {draft.league.season_id}
                    {isUserDraftAdmin(draft, userId) && (
                      <Badge variant="outline" className="ml-2">
                        Admin
                      </Badge>
                    )}
                  </div>

                  <div className="flex items-center justify-center gap-12">
                    <p className="m-0">{getDraftUserMember(draft, userId)?.team_name}</p>
                    <p className="m-0">{new Date(draft.draft_date).toLocaleDateString()}</p>
                  </div>

                  <div className="flex items-center justify-end gap-4">
                    {!draft.is_completed && (
                      <Link href={route('drafts.draft-room', draft.id)}>
                        <Button variant="outline">Draft Room</Button>
                      </Link>
                    )}
                    <Link href={route('drafts.show', [draft.league_id, draft.league.season_id])}>
                      <Button variant="outline">Draft Results</Button>
                    </Link>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}
