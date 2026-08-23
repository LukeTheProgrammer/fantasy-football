import { Badge } from '@/components/ui/badge';
import { type Team } from '@/types/models';

interface TeamBadgeProps {
    team: Team;
}

export function TeamBadge({ team }: TeamBadgeProps) {
  const teamColors = {
    ARZ: '151, 35, 63',
    ATL: '167, 25, 48',
    BAL: '26, 25, 95',
    BUF: '0, 51, 141',
    CAR: '0, 133, 202',
    CHI: '11, 22, 42',
    CIN: '251, 79, 20',
    CLE: '49, 29, 0',
    DAL: '0, 53, 148',
    DEN: '251, 79, 20',
    DET: '0, 118, 182',
    GB:  '24, 48, 40',
    HOU: '3, 32, 47',
    IND: '0, 44, 95',
    JAX: '16, 24, 32',
    KC:  '227, 24, 55',
    LAC: '0, 128, 198',
    LAR: '0, 53, 148',
    LV:  '0, 0, 0',
    MIA: '0, 142, 151',
    MIN: '79, 38, 131',
    NE:  '0, 34, 68',
    NO:  '211, 188, 141',
    NYG: '1, 35, 82',
    NYJ: '18, 87, 64',
    PHI: '0, 76, 84',
    PIT: '255, 182, 18',
    SEA: '0, 34, 68',
    SF:  '170, 0, 0',
    TB:  '213, 10, 10',
    TEN: '12, 35, 64',
    WAS: '90, 20, 20'
  };

  const getTeamColor = (team: string) => {
    return teamColors[team.toUpperCase()];
  };

  return (
    <Badge
      className="px-2 py-1 text-xs font-medium text-white"
      style={{ backgroundColor: `rgb(${getTeamColor(team.abbreviation)})` }}
    >
      <div className="size-6 flex items-center justify-center">
        {team.abbreviation}
      </div>
    </Badge>
  );
}
