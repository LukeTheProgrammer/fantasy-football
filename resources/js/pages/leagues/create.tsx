import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import LeagueForm from './form';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';


const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Create League',
    href: '/leagues/create',
  },
];

export default function CreateLeague() {

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create League" />

      <div className="flex-1 p-8">
        <Heading title="Create a New Fantasy League" description="Set up your new fantasy football league with custom settings" />
        
        <LeagueForm
          submitEndpoint="/api/leagues"
          submitMethod="post"
          submitButtonText="Create League"
          processingButtonText="Creating..."
          successMessage="Your fantasy league has been created successfully!"
          redirectPath="/dashboard"
        />
      </div>
    </AppLayout>
  );
}
