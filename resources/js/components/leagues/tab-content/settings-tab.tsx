import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { type LeagueResource } from '@/types/resources';

interface SettingsTabProps {
  league: LeagueResource;
}

export default function SettingsTab({ league }: SettingsTabProps) {
  const formatDate = (dateString?: string | null) => {
    if (!dateString) return 'Not scheduled';
    return new Date(dateString).toLocaleString();
  };

  return (
    <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
      {/* League Details Card */}
      <Card>
        <CardContent className="space-y-4">
          <div className="mb-8 grid w-full">
            <h2 className="text-lg font-semibold">League Info</h2>
            <p className="text-sm text-muted-foreground">Basic information about your fantasy football league.</p>
          </div>

          <dl className="space-y-4">
            <div>
              <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
              <dd className="mt-1">{league.description || 'No description provided'}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Type</dt>
              <dd className="mt-1 capitalize">{league.draft?.draft_type}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Date</dt>
              <dd className="mt-1">{formatDate(league.draft?.draft_date)}</dd>
            </div>
            <div>
              <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Visibility</dt>
              <dd className="mt-1">{league.is_public ? 'Public' : 'Private'}</dd>
            </div>
          </dl>

          <div className="mt-6">
            <h3 className="text-md mb-2 font-medium">Draft Status</h3>
            {league?.draft?.draft_date ? (
              <div className="space-y-2">
                <p>{new Date(league.draft.draft_date) > new Date() ? 'Draft scheduled for:' : 'Draft was scheduled for:'}</p>
                <p className="font-medium">{formatDate(league.draft.draft_date)}</p>
                {new Date(league.draft.draft_date) > new Date() && <Button className="mt-2 w-full">Enter Draft Room</Button>}
              </div>
            ) : (
              <p className="text-gray-500 dark:text-gray-400">Draft not yet scheduled</p>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Roster Settings Card */}
      <Card>
        <CardContent className="space-y-4">
          <div className="mb-8 grid w-full">
            <h2 className="text-lg font-semibold">Roster Settings</h2>
            <p className="text-sm text-muted-foreground">Your league's roster positions and size.</p>
          </div>

          <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Starters</h3>
              <p className="mt-1 text-lg font-semibold">{league.settings.starters_count}</p>
            </div>
            <div>
              <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Bench</h3>
              <p className="mt-1 text-lg font-semibold">{league.settings.bench_count}</p>
            </div>
            <div>
              <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">IR</h3>
              <p className="mt-1 text-lg font-semibold">{league.settings.ir_spots}</p>
            </div>
          </div>

          <div className="mt-6">
            <h3 className="text-md mb-2 font-medium">Roster Positions</h3>
            <div className="flex flex-wrap gap-2">
              {league.settings.roster_positions.map((position, index) => (
                <div key={index} className="rounded-md bg-gray-100 px-2 py-1 text-sm dark:bg-gray-700">
                  {position}
                </div>
              ))}
            </div>
          </div>

          <div className="mt-6">
            <h3 className="text-md mb-2 font-medium">Total Roster Size</h3>
            <p className="text-lg font-semibold">{league.settings.roster_size} players</p>
          </div>
        </CardContent>
      </Card>

      {/* Scoring Settings Card */}
      <Card>
        <CardContent className="space-y-4">
          <div className="mb-8 grid w-full">
            <h2 className="text-lg font-semibold">Scoring Settings</h2>
            <p className="text-sm text-muted-foreground">Your league's scoring rules.</p>
          </div>

          <div>
            <h3 className="text-lg font-medium">Passing</h3>
            <Separator className="my-2" />
            <div className="mt-2 grid grid-cols-2 gap-1">
              <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
              <div className="text-right">{league.settings.passing_points_per_yard}</div>
              <div className="text-sm text-gray-500 dark:text-gray-400">TD Pass</div>
              <div className="text-right">{league.settings.passing_td_points} pts</div>
              <div className="text-sm text-gray-500 dark:text-gray-400">Interception</div>
              <div className="text-right">{league.settings.interception_points} pts</div>
            </div>
          </div>

          <div className="mt-4">
            <h3 className="text-lg font-medium">Rushing</h3>
            <Separator className="my-2" />
            <div className="mt-2 grid grid-cols-2 gap-1">
              <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
              <div className="text-right">{league.settings.rushing_points_per_yard}</div>
              <div className="text-sm text-gray-500 dark:text-gray-400">TD</div>
              <div className="text-right">{league.settings.rushing_td_points} pts</div>
            </div>
          </div>

          <div className="mt-4">
            <h3 className="text-lg font-medium">Receiving</h3>
            <Separator className="my-2" />
            <div className="mt-2 grid grid-cols-2 gap-1">
              <div className="text-sm text-gray-500 dark:text-gray-400">Points per Yard</div>
              <div className="text-right">{league.settings.receiving_points_per_yard}</div>
              <div className="text-sm text-gray-500 dark:text-gray-400">TD</div>
              <div className="text-right">{league.settings.receiving_td_points} pts</div>
              <div className="text-sm text-gray-500 dark:text-gray-400">Reception</div>
              <div className="text-right">{league.settings.reception_points} pts</div>
            </div>
          </div>

          <div className="mt-4">
            <h3 className="text-lg font-medium">Miscellaneous</h3>
            <Separator className="my-2" />
            <div className="mt-2 grid grid-cols-2 gap-1">
              <div className="text-sm text-gray-500 dark:text-gray-400">Fumble Lost</div>
              <div className="text-right">{league.settings.fumble_lost_points} pts</div>
              <div className="text-sm text-gray-500 dark:text-gray-400">2-Point Conversion</div>
              <div className="text-right">{league.settings.two_point_conversion_points} pts</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
