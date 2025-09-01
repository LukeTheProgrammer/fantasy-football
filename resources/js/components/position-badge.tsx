import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { type Position } from '@/types/models';

interface PositionBadgeProps {
    position: Position;
}

export function PositionBadge({ position }: PositionBadgeProps) {
  const getPositionColor = (pos: string) => {
    switch (pos.toUpperCase()) {
      case 'QB':
        return 'bg-red-500 hover:bg-red-600 text-white';
      case 'RB':
        return 'bg-green-500 hover:bg-green-600 text-white';
      case 'WR':
        return 'bg-blue-500 hover:bg-blue-600 text-white';
      case 'TE':
        return 'bg-orange-500 hover:bg-orange-600 text-white';
      case 'K':
        return 'bg-purple-500 hover:bg-purple-600 text-white';
      case 'DEF':
        return 'bg-gray-700 hover:bg-gray-800 text-white';
      default:
        return 'bg-gray-500 hover:bg-gray-600 text-white';
    }
  };

  return (
    <Badge className={cn(
      'px-2 py-1 text-xs font-medium',
      getPositionColor(position.abbreviation)
    )}>
      <div className="size-6 flex items-center justify-center">
        {position.abbreviation}
      </div>
    </Badge>
  );
}
