import { ThemeProvider } from '@/common/appearance/ThemeProvider';
import { Toaster } from '@/components/ui/sonner';
import { AppSidebarLayout as AppLayoutTemplate } from '@/pages/layouts/app/AppSidebarLayout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
  children: ReactNode;
  breadcrumbs?: BreadcrumbItem[];
}

export const AppLayout = ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
  <ThemeProvider defaultTheme="dark" storageKey="vite-ui-theme">
    <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
      {children}
      <Toaster />
    </AppLayoutTemplate>
  </ThemeProvider>
);
