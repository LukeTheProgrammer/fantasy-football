import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Shield } from 'lucide-react';
import { type LeagueMember } from '@/types/models';
import { cn } from '@/lib/utils';

interface MemberBadgeProps {
  member: LeagueMember;
  onClick?: () => void;
  isSelected?: boolean;
}

export default function MemberBadge({ member, onClick, isSelected = false }: MemberBadgeProps) {
  return (
    <div 
      className={cn(
        "flex items-center justify-between rounded-md border p-3 cursor-pointer transition-colors",
        isSelected ? "border-primary bg-primary/10" : "hover:bg-accent"
      )}
      onClick={onClick}
    >
      <div className="flex items-center justify-between w-full">
        <div className="grow-0">
          <Avatar>
            {member.team_logo ? <AvatarImage src={member.team_logo} alt={member.team_name} /> : null}
            <AvatarFallback>{member.team_name.substring(0, 2).toUpperCase()}</AvatarFallback>
          </Avatar>
        </div>
        <div className="grow-1 grid grid-cols-4 pl-2">
          <div className="col-span-3">
            <p className="font-medium">{member.team_name}</p>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              {member.user?.name}
            </p>
          </div>
          <div className="col-span-1 flex items-start justify-end">
              {member?.is_admin && (
                <Badge variant="outline" className="ml-2">
                  <Shield size={12} className="mr-1" />
                  Admin
                </Badge>
              )}
          </div>
        </div>
      </div>
    </div>
  );
}
