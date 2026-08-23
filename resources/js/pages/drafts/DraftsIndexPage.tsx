import { AppLayout } from '@/pages/layouts/AppLayout';
import { Heading } from '@/common/heading/Heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { Plus } from 'lucide-react';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Draft } from '@/types/models';
import { getDraftUserMember } from '@/modules/drafts/helpers/getDraftUserMember';
import { isUserDraftAdmin } from '@/modules/drafts/helpers/isUserDraftAdmin';
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
          rightContent={(
            <Link href="/drafts/create">
              <Button size="lg" variant="secondary" className="cursor-pointer">
                <span className="flex justify-between">
                  <Plus className="h-5 w-5 pt-1" strokeWidth={4} />
                  <span className="pl-1">Create New Draft</span>
                </span>
              </Button>
            </Link>
          )}
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
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {drafts.map((draft) => (
              <Card key={draft.id} className="overflow-hidden">
                <CardHeader>
                  <CardTitle>{draft.league.name} {draft.league.season}</CardTitle>
                  <CardDescription>
                    {isUserDraftAdmin(draft, userId) && (
                      <Badge variant="outline" className="mr-2">
                        Admin
                      </Badge>
                    )}
                    {draft.draft_type === 'snake' ? 'Snake Draft' : 'Auction Draft'}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <span>Your team: {getDraftUserMember(draft, userId)?.team_name}</span>
                    <span className="mx-2">•</span>
                    <span>{isUserDraftAdmin(draft, userId) ? 'Admin' : 'Player'}</span>
                  </div>
                </CardContent>
                <CardFooter>
                  <Link href={route('drafts.show', draft.id)} className="w-full">
                    <Button variant="outline" className="w-full">
                      View Draft
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
