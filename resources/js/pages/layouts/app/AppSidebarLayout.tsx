import { AppContent } from '@/modules/app-shell/components/AppContent';
import { AppShell } from '@/modules/app-shell/components/AppShell';
import { AppSidebar } from '@/modules/app-shell/components/AppSidebar';
import { AppSidebarHeader } from '@/modules/app-shell/components/AppSidebarHeader';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

interface Props {
  breadcrumbs?: BreadcrumbItem[];
  actionItem?: React.ReactNode;
}

export function AppSidebarLayout({ children, breadcrumbs = [], actionItem }: PropsWithChildren<Props>) {
  return (
    <AppShell variant="sidebar">
      <AppSidebar />
      <AppContent variant="sidebar" className="overflow-x-hidden">
        <AppSidebarHeader breadcrumbs={breadcrumbs} actionItem={actionItem} />
        {children}
      </AppContent>
    </AppShell>
  );
}
