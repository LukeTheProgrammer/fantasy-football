import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import DraftForm from './form';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';


const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Create Draft',
    href: '/drafts/create',
  },
];

export default function CreateDraft() {

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Draft" />

      <div className="flex-1 p-8">
        <Heading
          title="Create a New Fantasy Draft"
          description="Set up your new fantasy football draft with custom settings"
        />

        <DraftForm
          submitEndpoint="/api/drafts"
          submitMethod="post"
          submitButtonText="Create Draft"
          processingButtonText="Creating..."
          successMessage="Your fantasy draft has been created successfully!"
          redirectPath="/drafts"
        />
      </div>
    </AppLayout>
  );
}
