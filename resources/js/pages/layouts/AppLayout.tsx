import { ThemeProvider } from '@/common/appearance/ThemeProvider';
import { Toaster } from '@/components/ui/sonner';
import { AppSidebarLayout as AppLayoutTemplate } from '@/pages/layouts/app/AppSidebarLayout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
  children: ReactNode;
  breadcrumbs?: BreadcrumbItem[];
  actionItem?: React.ReactNode;
}

export const AppLayout = ({ children, breadcrumbs, actionItem, ...props }: AppLayoutProps) => (
  <ThemeProvider defaultTheme="dark" storageKey="vite-ui-theme">
    <AppLayoutTemplate breadcrumbs={breadcrumbs} actionItem={actionItem} {...props}>
      {children}
      <Toaster />
    </AppLayoutTemplate>
  </ThemeProvider>
);
