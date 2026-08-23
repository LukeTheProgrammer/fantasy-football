import { AppContent } from '@/modules/app-shell/components/AppContent';
import { AppHeader } from '@/modules/app-shell/components/AppHeader';
import { AppShell } from '@/modules/app-shell/components/AppShell';
import { type BreadcrumbItem } from '@/types';
import type { PropsWithChildren } from 'react';

export function AppHeaderLayout({ children, breadcrumbs }: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
  return (
    <AppShell>
      <AppHeader breadcrumbs={breadcrumbs} />
      <AppContent>{children}</AppContent>
    </AppShell>
  );
}
