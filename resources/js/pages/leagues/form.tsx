import RosterPositionsEditor from '@/components/form/roster-positions';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import { useCallback, useEffect, useState } from 'react';

// Helper function to safely access nested error properties
const getNestedError = (errors: Record<string, unknown> | null | undefined, key: string): string | undefined => {
  return errors && typeof errors === 'object' ? (errors[key] as string | undefined) : undefined;
};

interface LeagueFormData {
  name: string;
  description: string;
  team_count: number;
  is_public: boolean;
  draft_type: string;
  draft_date: string;
  settings: {
    roster_positions: string[];
    roster_size: number;
    starters_count: number;
    bench_count: number;
    ir_spots: number;
    passing_points_per_yard: number;
    passing_td_points: number;
    interception_points: number;
    rushing_points_per_yard: number;
    rushing_td_points: number;
    receiving_points_per_yard: number;
    receiving_td_points: number;
    reception_points: number;
    fumble_lost_points: number;
    two_point_conversion_points: number;
  };
}

interface LeagueFormProps {
  initialData?: LeagueFormData;
  submitEndpoint: string;
  submitMethod: 'post' | 'patch';
  submitButtonText: string;
  processingButtonText: string;
  successMessage: string;
  redirectPath: string;
  onSuccess?: (data: any) => void;
}

const defaultFormData: LeagueFormData = {
  name: '',
  description: '',
  team_count: 10,
  is_public: false,
  draft_type: 'snake',
  draft_date: '',
  settings: {
    roster_positions: ['QB', 'RB', 'RB', 'WR', 'WR', 'TE', 'FLEX', 'K', 'DEF'],
    roster_size: 16,
    starters_count: 9,
    bench_count: 7,
    ir_spots: 1,
    passing_points_per_yard: 0.04,
    passing_td_points: 4,
    interception_points: -2,
    rushing_points_per_yard: 0.1,
    rushing_td_points: 6,
    receiving_points_per_yard: 0.1,
    receiving_td_points: 6,
    reception_points: 0,
    fumble_lost_points: -2,
    two_point_conversion_points: 2,
  },
};

export default function LeagueForm({
  initialData,
  submitEndpoint,
  submitMethod,
  submitButtonText,
  processingButtonText,
  successMessage,
  redirectPath,
  onSuccess,
}: LeagueFormProps) {
  const [data, setData] = useState<LeagueFormData>(initialData || defaultFormData);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [processing, setProcessing] = useState(false);

  // Update roster size when starters or bench count changes
  useEffect(() => {
    const rosterSize = data.settings.starters_count + data.settings.bench_count;
    setData(prev => ({
      ...prev,
      settings: {
        ...prev.settings,
        roster_size: rosterSize,
      }
    }));
  }, [data.settings.starters_count, data.settings.bench_count]);

  // Handle form submission
  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});
    setValidationErrors({});

    try {
      const response = await axios({
        method: submitMethod,
        url: submitEndpoint,
        data,
      });

      toast.success(successMessage);

      if (onSuccess) {
        onSuccess(response.data);
      } else {
        router.visit(redirectPath);
      }
    } catch (error: any) {
      if (error.response?.status === 422) {
        setValidationErrors(error.response.data.errors || {});
        toast.error('Please fix the validation errors.');
      } else {
        toast.error('An error occurred. Please try again.');
      }
    } finally {
      setProcessing(false);
    }
  }, [data, submitMethod, submitEndpoint, successMessage, redirectPath, onSuccess]);

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-3 gap-6">
        <Card>
          <CardContent>
            <div className="mb-6">
              <h2 className="text-lg font-medium">League Details</h2>
              <p className="text-sm text-muted-foreground">Basic information about your league.</p>
            </div>

            <div className="space-y-4">
              <div>
                <Label htmlFor="name">League Name</Label>
                <Input
                  id="name"
                  value={data.name}
                  onChange={(e) => setData(prev => ({ ...prev, name: e.target.value }))}
                  className="mt-1"
                />
                {(errors.name || validationErrors.name) && (
                  <p className="text-sm text-red-500 mt-1">{errors.name || validationErrors.name}</p>
                )}
              </div>

              <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={data.description}
                  onChange={(e) => setData(prev => ({ ...prev, description: e.target.value }))}
                  className="mt-1"
                />
                {(errors.description || validationErrors.description) && (
                  <p className="text-sm text-red-500 mt-1">{errors.description || validationErrors.description}</p>
                )}
              </div>

              <div>
                <Label htmlFor="team_count">Number of Teams</Label>
                <Input
                  id="team_count"
                  type="number"
                  min={2}
                  max={32}
                  value={data.team_count}
                  onChange={(e) => setData(prev => ({ ...prev, team_count: parseInt(e.target.value) || 0 }))}
                  className="mt-1"
                />
                {(errors.team_count || validationErrors.team_count) && (
                  <p className="text-sm text-red-500 mt-1">{errors.team_count || validationErrors.team_count}</p>
                )}
              </div>

              <div>
                <Label htmlFor="draft_type">Draft Type</Label>
                <Select
                  value={data.draft_type}
                  onValueChange={(value) => setData(prev => ({ ...prev, draft_type: value }))}
                >
                  <SelectTrigger id="draft_type" className="mt-1">
                    <SelectValue placeholder="Select draft type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="snake">Snake</SelectItem>
                    <SelectItem value="auction">Auction</SelectItem>
                  </SelectContent>
                </Select>
                {(errors.draft_type || validationErrors.draft_type) && (
                  <p className="text-sm text-red-500 mt-1">{errors.draft_type || validationErrors.draft_type}</p>
                )}
              </div>

              <div>
                <Label htmlFor="draft_date">Draft Date</Label>
                <Input
                  id="draft_date"
                  type="datetime-local"
                  value={data.draft_date}
                  onChange={(e) => setData(prev => ({ ...prev, draft_date: e.target.value }))}
                  className="mt-1"
                />
                {(errors.draft_date || validationErrors.draft_date) && (
                  <p className="text-sm text-red-500 mt-1">{errors.draft_date || validationErrors.draft_date}</p>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <div className="mb-6">
              <h2 className="text-lg font-medium">Roster Settings</h2>
              <p className="text-sm text-muted-foreground">Configure your league's roster positions and size.</p>
            </div>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <Label htmlFor="starters_count">Starters</Label>
                <Input
                  id="starters_count"
                  type="number"
                  min={1}
                  value={data.settings.starters_count}
                  onChange={(e) =>
                    setData(prev => ({
                      ...prev,
                      settings: {
                        ...prev.settings,
                        starters_count: parseInt(e.target.value) || 0,
                      }
                    }))
                  }
                />
                {(getNestedError(errors, 'settings.starters_count') || validationErrors['settings.starters_count']) && (
                  <p className="text-sm text-red-500 mt-1">{getNestedError(errors, 'settings.starters_count') || validationErrors['settings.starters_count']}</p>
                )}
              </div>
              <div>
                <Label htmlFor="bench_count">Bench</Label>
                <Input
                  id="bench_count"
                  type="number"
                  min={0}
                  value={data.settings.bench_count}
                  onChange={(e) =>
                    setData(prev => ({
                      ...prev,
                      settings: {
                        ...prev.settings,
                        bench_count: parseInt(e.target.value) || 0,
                      }
                    }))
                  }
                />
                {(getNestedError(errors, 'settings.bench_count') || validationErrors['settings.bench_count']) && (
                  <p className="text-sm text-red-500 mt-1">{getNestedError(errors, 'settings.bench_count') || validationErrors['settings.bench_count']}</p>
                )}
              </div>
              <div>
                <Label htmlFor="ir_spots">IR</Label>
                <Input
                  id="ir_spots"
                  type="number"
                  min={0}
                  value={data.settings.ir_spots}
                  onChange={(e) =>
                    setData(prev => ({
                      ...prev,
                      settings: {
                        ...prev.settings,
                        ir_spots: parseInt(e.target.value) || 0,
                      }
                    }))
                  }
                />
                {(getNestedError(errors, 'settings.ir_spots') || validationErrors['settings.ir_spots']) && (
                  <p className="text-sm text-red-500 mt-1">{getNestedError(errors, 'settings.ir_spots') || validationErrors['settings.ir_spots']}</p>
                )}
              </div>
            </div>

            <div className="mt-4">
              <Label htmlFor="roster_positions">Roster Positions</Label>
              <RosterPositionsEditor
                positions={data.settings.roster_positions}
                onChange={(positions) =>
                  setData(prev => ({
                    ...prev,
                    settings: {
                      ...prev.settings,
                      roster_positions: positions,
                    }
                  }))
                }
              />
              {(getNestedError(errors, 'settings.roster_positions') || validationErrors['settings.roster_positions']) && (
                <p className="text-sm text-red-500 mt-1">{getNestedError(errors, 'settings.roster_positions') || validationErrors['settings.roster_positions']}</p>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <div className="mb-6">
              <h2 className="text-lg font-medium">Scoring Settings</h2>
              <p className="text-sm text-muted-foreground">Configure your league's scoring rules.</p>
            </div>

            <div className="mt-2 grid w-full grid-cols-1 lg:grid-cols-2 gap-4">
              <div>
                <h3 className="text-lg font-medium">Passing</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid w-full grid-cols-2 gap-1">
                  <div className="col-span-1">
                    <Label>per Yard</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.01"
                      value={data.settings.passing_points_per_yard}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            passing_points_per_yard: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                  <div className="col-span-1">
                    <Label>TD Pass</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.5"
                      value={data.settings.passing_td_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            passing_td_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                  <div className="col-span-1">
                    <Label>INT</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.5"
                      value={data.settings.interception_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            interception_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                </div>
              </div>

              <div>
                <h3 className="text-lg font-medium">Receiving</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="col-span-1">
                    <Label>per Yard</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.01"
                      value={data.settings.receiving_points_per_yard}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            receiving_points_per_yard: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                  <div className="col-span-1">
                    <Label>TD Rec</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.5"
                      value={data.settings.receiving_td_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            receiving_td_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                  <div className="col-span-1">
                    <Label>Reception</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.01"
                      value={data.settings.reception_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            reception_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                </div>
              </div>
            </div>

            <div className="mt-2 grid w-full grid-cols-1 lg:grid-cols-2 gap-4">
              <div className="mt-2">
                <h3 className="text-lg font-medium">Rushing</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="col-span-1">
                    <Label>per Yard</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.01"
                      value={data.settings.rushing_points_per_yard}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            rushing_points_per_yard: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                  <div className="col-span-1">
                    <Label>TD Rush</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.5"
                      value={data.settings.rushing_td_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            rushing_td_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                </div>
              </div>

              <div className="mt-2">
                <h3 className="text-lg font-medium">Misc</h3>
                <Separator className="my-2" />
                <div className="mt-2 grid grid-cols-2 gap-1">
                  <div className="col-span-1">
                    <Label>Fumbles</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.5"
                      value={data.settings.fumble_lost_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            fumble_lost_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                  <div className="col-span-1">
                    <Label>2-Pt Conv</Label>
                  </div>
                  <div className="col-span-1">
                    <Input
                      type="number"
                      step="0.5"
                      value={data.settings.two_point_conversion_points}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            two_point_conversion_points: parseFloat(e.target.value) || 0,
                          }
                        }))
                      }
                    />
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="flex justify-end space-x-2">
        <Button type="submit" disabled={processing}>
          {processing ? processingButtonText : submitButtonText}
        </Button>
      </div>
    </form>
  );
}
