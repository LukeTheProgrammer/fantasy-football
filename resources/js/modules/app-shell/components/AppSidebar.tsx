import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { AppLogo } from '@/modules/app-shell/components/AppLogo';
import { NavFooter } from '@/modules/app-shell/components/NavFooter';
import { NavMain } from '@/modules/app-shell/components/NavMain';
import { NavUser } from '@/modules/app-shell/components/NavUser';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, ChartNoAxesCombined, Folder, Gauge, Trophy, UserRoundPlus, UsersRound } from 'lucide-react';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: Gauge,
  },
  {
    title: 'Leagues',
    href: '/leagues',
    icon: Trophy,
  },
  {
    title: 'Drafts',
    href: '/drafts',
    icon: UserRoundPlus,
  },
  {
    title: 'Draft Rankings',
    href: '/rankings',
    icon: ChartNoAxesCombined,
  },
  {
    title: 'Players',
    href: '/players',
    icon: UsersRound,
  },
];

const footerNavItems: NavItem[] = [
  {
    title: 'Repository',
    href: 'https://github.com/laravel/react-starter-kit',
    icon: Folder,
  },
  {
    title: 'Documentation',
    href: 'https://laravel.com/docs/starter-kits#react',
    icon: BookOpen,
  },
];

export function AppSidebar() {
  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/dashboard" prefetch>
                <AppLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <NavMain items={mainNavItems} />
      </SidebarContent>

      <SidebarFooter>
        <NavFooter items={footerNavItems} className="mt-auto" />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
