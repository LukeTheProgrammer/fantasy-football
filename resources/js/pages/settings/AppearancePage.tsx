import { Head } from '@inertiajs/react';

import { AppearanceToggleTab as AppearanceTabs } from '@/common/appearance/AppearanceToggleTab';
import { HeadingSmall } from '@/common/heading/HeadingSmall';
import { type BreadcrumbItem } from '@/types';

import { AppLayout } from '@/pages/layouts/AppLayout';
import { SettingsLayout } from '@/pages/settings/layouts/SettingsLayout';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Appearance settings',
    href: '/settings/appearance',
  },
];

export default function Appearance() {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Appearance settings" />

      <SettingsLayout>
        <div className="space-y-6">
          <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
          <AppearanceTabs />
        </div>
      </SettingsLayout>
    </AppLayout>
  );
}
