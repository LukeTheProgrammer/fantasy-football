import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type RankingFormat } from '@/types/models';
import { router } from '@inertiajs/react';

interface RankingFormatSelectProps {
  formats: RankingFormat[];
  format: RankingFormat;
  routeName: string;
  className?: string;
}

/**
 * Switches which scoring format's rankings are shown. A player is ranked once
 * per format, so the format is chosen on the server rather than filtered here.
 */
export function RankingFormatSelect({ formats, format, routeName, className = 'w-[11rem]' }: RankingFormatSelectProps) {
  if (formats.length <= 1) {
    return null;
  }

  const handleFormatChange = (value: string) => {
    router.visit(route(routeName, { format: value }), {
      preserveScroll: true,
    });
  };

  return (
    <Select value={format.key} onValueChange={handleFormatChange}>
      <SelectTrigger className={className}>
        <SelectValue placeholder="Scoring format" />
      </SelectTrigger>
      <SelectContent>
        {formats.map((option) => (
          <SelectItem key={option.key} value={option.key}>
            {option.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
}
