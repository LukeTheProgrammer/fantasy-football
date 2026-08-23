import { AppLogoIcon } from '@/modules/app-shell/components/AppLogoIcon';
import { Blender } from 'lucide-react';

export function AppLogo() {
  return (
    <>
      <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <Blender />
      </div>
      <div className="ml-1 grid flex-1 text-left text-sm">
        <span className="mb-0.5 truncate leading-tight font-semibold">Laravel Starter Kit</span>
      </div>
    </>
  );
}
