import { router } from '@inertiajs/react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type SeasonOption } from '@/types/models';

interface SeasonSelectProps {
  seasons: SeasonOption[];
  season: number;
  routeName: string;
  className?: string;
}

/**
 * Switches between the seasons of a league. Each season is a separate record,
 * so choosing one navigates to that season's page rather than filtering.
 */
export function SeasonSelect({ seasons, season, routeName, className = 'w-[8rem]' }: SeasonSelectProps) {
  if (seasons.length <= 1) {
    return null;
  }

  const handleSeasonChange = (value: string) => {
    const selected = seasons.find((option) => option.season.toString() === value);

    if (selected) {
      router.visit(route(routeName, selected.id));
    }
  };

  return (
    <Select value={season?.toString()} onValueChange={handleSeasonChange}>
      <SelectTrigger className={className}>
        <SelectValue placeholder="Season" />
      </SelectTrigger>
      <SelectContent>
        {seasons.map((option) => (
          <SelectItem key={option.id} value={option.season.toString()}>
            {option.season}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
}
