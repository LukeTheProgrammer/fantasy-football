import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import DraftForm from './form';
import { Head } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';
import { type BreadcrumbItem } from '@/types';

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
    title: 'Edit Draft',
    href: '#',
  },
];

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
}

interface EditDraftProps extends PageProps {
  draft: Draft;
}

export default function EditDraft({ draft }: EditDraftProps) {
  // Map Draft to DraftFormData
  const formData = {
    league_id: draft.league_id,
    draft_date: draft.draft_date,
    draft_type: draft.draft_type,
    is_completed: draft.is_completed,
    auction_budget: draft.auction_budget,
    current_pick: draft.current_pick,
    current_round: draft.current_round,
    time_per_pick: draft.time_per_pick,
    is_active: draft.is_active,
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Edit Draft" />

      <div className="flex-1 p-8">
        <Heading title="Edit Fantasy Draft" description="Update your fantasy football draft settings" />

        <DraftForm
          initialData={formData}
          submitEndpoint={`/api/drafts/${draft.id}`}
          submitMethod="patch"
          submitButtonText="Update Draft"
          processingButtonText="Updating..."
          successMessage="Your fantasy draft has been updated successfully!"
          redirectPath={`/drafts/${draft.id}`}
          onSuccess={() => true}
        />
      </div>
    </AppLayout>
  );
}
