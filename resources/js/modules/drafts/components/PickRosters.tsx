import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { PositionBadge } from '@/modules/players/components/PositionBadge';
import { type TeamRoster } from '@/types/picks';
import { useState } from 'react';

interface PickRostersProps {
  rosters: TeamRoster[];
  onTheClockMemberId: number | null;
}

/**
 * What each team holds. Keepers and picks are kept apart because a keeper
 * cost no pick and reading them as one list hides that.
 */
export function PickRosters({ rosters, onTheClockMemberId }: PickRostersProps) {
  const [selected, setSelected] = useState(() => String(onTheClockMemberId ?? rosters[0]?.league_member_id ?? ''));

  return (
    <Card>
      <CardHeader>
        <CardTitle>Rosters</CardTitle>
      </CardHeader>
      <CardContent>
        <Tabs value={selected} onValueChange={setSelected}>
          <TabsList className="flex h-auto flex-wrap justify-start">
            {rosters.map((roster) => (
              <TabsTrigger key={roster.league_member_id} value={String(roster.league_member_id)} className="text-xs">
                {roster.team_name}
              </TabsTrigger>
            ))}
          </TabsList>

          {rosters.map((roster) => (
            <TabsContent key={roster.league_member_id} value={String(roster.league_member_id)}>
              <p className="mb-2 text-sm text-muted-foreground">{roster.owner_name}</p>

              <p className="mt-4 mb-1 text-xs tracking-wide text-muted-foreground uppercase">Keepers &middot; {roster.keepers.length}</p>
              <ul className="space-y-1">
                {roster.keepers.map((player) => (
                  <li key={player.player_id} className="flex items-center gap-2 text-sm">
                    <PositionBadge position={player.position ?? ''} />
                    <span>{player.full_name}</span>
                    <span className="text-muted-foreground">{player.team}</span>
                  </li>
                ))}
              </ul>

              <p className="mt-4 mb-1 text-xs tracking-wide text-muted-foreground uppercase">Drafted &middot; {roster.picks.length}</p>
              {roster.picks.length === 0 && <p className="text-sm text-muted-foreground">No picks yet.</p>}
              <ul className="space-y-1">
                {roster.picks.map((pick) => (
                  <li key={pick.pick_id} className="flex items-center gap-2 text-sm">
                    <span className="w-10 text-xs text-muted-foreground">R{pick.round}</span>
                    <PositionBadge position={pick.position ?? ''} />
                    <span>{pick.full_name}</span>
                    <span className="text-muted-foreground">{pick.team}</span>
                  </li>
                ))}
              </ul>
            </TabsContent>
          ))}
        </Tabs>
      </CardContent>
    </Card>
  );
}
