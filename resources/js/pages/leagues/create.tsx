// import { Alert, AlertDescription } from '@/components/ui/alert';
import Heading from '@/components/heading';
import RosterPositionsEditor from '@/components/leagues/RosterPositionsEditor';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

// Helper function to safely access nested error properties
const getNestedError = (errors: Record<string, unknown> | null | undefined, key: string): string | undefined => {
  return errors && typeof errors === 'object' ? (errors[key] as string | undefined) : undefined;
};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

export default function CreateLeague() {
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [formSubmitted, setFormSubmitted] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});
  
  const [data, setData] = useState({
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
      reception_points: 0.5,
      fumble_lost_points: -2,
      two_point_conversion_points: 2,
      field_goal_0_39_points: 3,
      field_goal_40_49_points: 4,
      field_goal_50_plus_points: 5,
      extra_point_points: 1,
      defense_sack_points: 1,
      defense_interception_points: 2,
      defense_fumble_recovery_points: 2,
      defense_td_points: 6,
      defense_safety_points: 2,
      defense_points_allowed_tiers: {
        '0': 10,
        '1-6': 7,
        '7-13': 4,
        '14-20': 1,
        '21-27': 0,
        '28-34': -1,
        '35+': -4,
      },
    },
  });

  // Function to update roster_size based on starters_count and bench_count
  const updateRosterSize = useCallback(() => {
    const startersCount = data.settings.starters_count || 0;
    const benchCount = data.settings.bench_count || 0;

    const newRosterSize = startersCount + benchCount;

    setData(prevData => ({
      ...prevData,
      settings: {
        ...prevData.settings,
        roster_size: newRosterSize
      }
    }));
  }, [data.settings, setData]);

  // Client-side validation
  const validateForm = useCallback(() => {
    const errors: Record<string, string> = {};

    // General tab validation
    if (!data.name.trim()) {
      errors.name = 'League name is required';
    } else if (data.name.length < 3) {
      errors.name = 'League name must be at least 3 characters';
    } else if (data.name.length > 50) {
      errors.name = 'League name must be less than 50 characters';
    }

    if (data.team_count < 2 || data.team_count > 32) {
      errors.team_count = 'League must have between 2 and 32 teams';
    }

    if (data.draft_date) {
      const draftDate = new Date(data.draft_date);
      const now = new Date();
      if (draftDate < now) {
        errors.draft_date = 'Draft date cannot be in the past';
      }
    }

    // Roster tab validation
    if (!data.settings.roster_positions.length) {
      errors['settings.roster_positions'] = 'At least one roster position is required';
    }

    if (data.settings.starters_count < 1) {
      errors['settings.starters_count'] = 'At least one starter is required';
    }

    // Scoring tab validation - ensure all values are numbers
    const scoringFields = [
      'passing_points_per_yard',
      'passing_td_points',
      'interception_points',
      'rushing_points_per_yard',
      'rushing_td_points',
      'receiving_points_per_yard',
      'receiving_td_points',
      'reception_points',
      'fumble_lost_points',
      'two_point_conversion_points',
    ];

    scoringFields.forEach((field) => {
      const value = data.settings[field as keyof typeof data.settings];
      if (typeof value !== 'number' || isNaN(value)) {
        errors[`settings.${field}`] = 'Must be a valid number';
      }
    });

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  }, [data]);

  // Update validation on data change if form was already submitted
  useEffect(() => {
    if (formSubmitted) {
      validateForm();
    }
  }, [data, formSubmitted, validateForm]);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setFormSubmitted(true);

    if (!validateForm()) {
      // Show a toast for each validation error
      const errorMessages = Object.entries(validationErrors);

      if (errorMessages.length > 0) {
        // Show general error message
        toast.error('Please fix the form errors before submitting');

        // Show specific error messages (limit to first 3 to avoid overwhelming the user)
        errorMessages.slice(0, 3).forEach(([field, message]) => {
          const fieldName = field.includes('.') ? field.split('.')[1] : field;
          toast.error(`${fieldName}: ${message}`);
        });

        // If there are more errors, show a count
        if (errorMessages.length > 3) {
          toast.error(`And ${errorMessages.length - 3} more errors...`);
        }
      }
      return;
    }
    
    setProcessing(true);
    
    axios.post('/api/leagues', data)
      .then(() => {
        // Reset form data
        setData({
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
            reception_points: 0.5,
            fumble_lost_points: -2,
            two_point_conversion_points: 2,
            field_goal_0_39_points: 3,
            field_goal_40_49_points: 4,
            field_goal_50_plus_points: 5,
            extra_point_points: 1,
            defense_sack_points: 1,
            defense_interception_points: 2,
            defense_fumble_recovery_points: 2,
            defense_td_points: 6,
            defense_safety_points: 2,
            defense_points_allowed_tiers: {
              '0': 10,
              '1-6': 7,
              '7-13': 4,
              '14-20': 1,
              '21-27': 0,
              '28-34': -1,
              '35+': -4,
            },
          },
        });
        setFormSubmitted(false);
        setProcessing(false);
        toast.success('Your fantasy league has been created successfully!');
        
        // Optionally redirect to the leagues page
        window.location.href = '/dashboard';
      })
      .catch(error => {
        setProcessing(false);
        if (error.response && error.response.data && error.response.data.errors) {
          setErrors(error.response.data.errors);
        }
        toast.error('There was a problem creating your league. Please check the form and try again.');
      });
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create League" />

      <div className="flex-1 p-8">
        <Heading title="Create a New Fantasy League" description="Set up your new fantasy football league with custom settings" />

        <form onSubmit={handleSubmit}>
          <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
            <Card>
              <CardContent className="space-y-4">
                <div className="mb-8 grid w-full">
                  <h2 className="text-lg font-semibold">League Details</h2>
                  <p className="text-sm text-muted-foreground">Set up the basic information for your fantasy football league.</p>
                </div>

                <div className="mb-8 grid w-full items-center gap-2">
                  <Label htmlFor="name">League Name</Label>
                  <Input id="name" value={data.name} onChange={(e) => setData(prev => ({ ...prev, name: e.target.value }))} placeholder="Enter league name" />
                  {(errors.name || validationErrors.name) && <p className="text-sm text-red-500">{errors.name || validationErrors.name}</p>}
                </div>

                <div className="mb-8 grid w-full items-center gap-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={data.description || ''}
                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData(prev => ({ ...prev, description: e.target.value }))}
                    placeholder="Describe your league"
                    rows={3}
                  />
                  {(errors.description || validationErrors.description) && (
                    <p className="text-sm text-red-500">{errors.description || validationErrors.description}</p>
                  )}
                </div>

                <div className="mb-8 grid w-full items-center gap-2">
                  <Label htmlFor="team_count">Number of Teams</Label>
                  <Input
                    id="team_count"
                    type="number"
                    min={2}
                    max={32}
                    value={data.team_count}
                    onChange={(e) => setData(prev => ({ ...prev, team_count: Number(e.target.value) }))}
                  />
                  {(errors.team_count || validationErrors.team_count) && (
                    <p className="text-sm text-red-500">{errors.team_count || validationErrors.team_count}</p>
                  )}
                </div>

                <div className="mb-8 grid w-full items-center gap-2">
                  <Label htmlFor="draft_type">Draft Type</Label>
                  <Select value={data.draft_type} onValueChange={(value) => setData(prev => ({ ...prev, draft_type: value }))}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select draft type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="snake">Snake</SelectItem>
                      <SelectItem value="auction">Auction</SelectItem>
                    </SelectContent>
                  </Select>
                  {(errors.draft_type || validationErrors.draft_type) && (
                    <p className="text-sm text-red-500">{errors.draft_type || validationErrors.draft_type}</p>
                  )}
                </div>

                <div className="mb-8 grid w-full items-center gap-2">
                  <Label htmlFor="draft_date">Draft Date & Time</Label>
                  <Input id="draft_date" type="datetime-local" value={data.draft_date} onChange={(e) => setData(prev => ({ ...prev, draft_date: e.target.value }))} />
                  {(errors.draft_date || validationErrors.draft_date) && (
                    <p className="text-sm text-red-500">{errors.draft_date || validationErrors.draft_date}</p>
                  )}
                </div>

                {/*
                <div className="mb-8 flex items-center space-x-2">
                  <Switch id="is_public" checked={data.is_public} onCheckedChange={(checked: boolean) => setData(prev => ({ ...prev, is_public: checked }))} />
                  <Label htmlFor="is_public">Make league public</Label>
                  {(errors.is_public || validationErrors.is_public) && (
                    <p className="text-sm text-red-500">{errors.is_public || validationErrors.is_public}</p>
                  )}
                </div>
                */}
              </CardContent>
            </Card>
            <Card>
              <CardContent className="space-y-4">
                <div className="mb-8 grid w-full">
                  <h2 className="text-lg font-semibold">Roster Settings</h2>
                  <p className="text-sm text-muted-foreground">Configure your league's roster positions and size.</p>
                </div>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                  {/*
                    <div>
                      <Label htmlFor="roster_size">Roster Size</Label>
                      <Input
                      id="roster_size"
                      type="number"
                      min={1}
                      value={data.settings.roster_size}
                      onChange={(e) =>
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            roster_size: parseInt(e.target.value) || 0,
                          }
                        }))
                      }
                      />
                      {(getNestedError(errors, 'settings.roster_size') || validationErrors['settings.roster_size']) && (
                        <p className="text-sm text-red-500 mt-1">{getNestedError(errors, 'settings.roster_size') || validationErrors['settings.roster_size']}</p>
                      )}
                    </div>
                    */}
                  <div>
                    <Label htmlFor="starters_count">Starting Players</Label>
                    <Input
                      id="starters_count"
                      type="number"
                      min={1}
                      value={data.settings.starters_count}
                      onChange={(e) => {
                        const value = parseInt(e.target.value) || 0;
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            starters_count: value,
                          }
                        }));
                        setTimeout(updateRosterSize, 0);
                      }}
                    />
                    {(getNestedError(errors, 'settings.starters_count') || validationErrors['settings.starters_count']) && (
                      <p className="mt-1 text-sm text-red-500">
                        {getNestedError(errors, 'settings.starters_count') || validationErrors['settings.starters_count']}
                      </p>
                    )}
                  </div>
                  <div>
                    <Label htmlFor="bench_count">Bench Spots</Label>
                    <Input
                      id="bench_count"
                      type="number"
                      min={0}
                      value={data.settings.bench_count}
                      onChange={(e) => {
                        const value = parseInt(e.target.value) || 0;
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            bench_count: value,
                          }
                        }));
                        setTimeout(updateRosterSize, 0);
                      }}
                    />
                    {(getNestedError(errors, 'settings.bench_count') || validationErrors['settings.bench_count']) && (
                      <p className="mt-1 text-sm text-red-500">
                        {getNestedError(errors, 'settings.bench_count') || validationErrors['settings.bench_count']}
                      </p>
                    )}
                  </div>
                  <div>
                    <Label htmlFor="ir_spots">IR Spots</Label>
                    <Input
                      id="ir_spots"
                      type="number"
                      min={0}
                      value={data.settings.ir_spots}
                      onChange={(e) => {
                        const value = parseInt(e.target.value) || 0;
                        setData(prev => ({
                          ...prev,
                          settings: {
                            ...prev.settings,
                            ir_spots: value,
                          }
                        }));
                        setTimeout(updateRosterSize, 0);
                      }}
                    />
                    {(getNestedError(errors, 'settings.ir_spots') || validationErrors['settings.ir_spots']) && (
                      <p className="mt-1 text-sm text-red-500">
                        {getNestedError(errors, 'settings.ir_spots') || validationErrors['settings.ir_spots']}
                      </p>
                    )}
                  </div>
                </div>

                <div>
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
                    <p className="mt-1 text-sm text-red-500">
                      {getNestedError(errors, 'settings.roster_positions') || validationErrors['settings.roster_positions']}
                    </p>
                  )}
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="space-y-4">
                <div className="mb-8 grid w-full">
                  <h2 className="text-lg font-semibold">Scoring Settings</h2>
                  <p className="text-sm text-muted-foreground">Configure your league's scoring rules.</p>
                </div>

                <div className="mt-2 w-full grid grid-cols-2 gap-4">
                  <div>
                    <h3 className="text-lg font-medium">Passing</h3>
                    <Separator className="my-2" />
                    <div className="mt-2 w-full grid grid-cols-3 gap-1">
                      <div className="col-span-2">
                        <Label>Points per Yard</Label>
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
                                passing_points_per_yard: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                      <div className="col-span-2">
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
                                passing_td_points: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                      <div className="col-span-2">
                        <Label>Interception</Label>
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
                                interception_points: parseFloat(e.target.value),
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
                    <div className="mt-2 grid grid-cols-3 gap-1">
                      <div className="col-span-2">
                        <Label>Yards per Point</Label>
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
                                receiving_points_per_yard: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                      <div className="col-span-2">
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
                                receiving_td_points: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                      <div className="col-span-2">
                        <Label>Reception Points</Label>
                      </div>
                      <div className="col-span-1">
                        <Input
                          type="number"
                          step="0.1"
                          value={data.settings.reception_points}
                          onChange={(e) =>
                            setData(prev => ({
                              ...prev,
                              settings: {
                                ...prev.settings,
                                reception_points: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <div className="mt-2 w-full grid grid-cols-2 gap-4">
                  <div className="mt-4">
                    <h3 className="text-lg font-medium">Rushing</h3>
                    <Separator className="my-2" />
                    <div className="mt-2 grid grid-cols-3 gap-1">
                      <div className="col-span-2">
                        <Label>Yards per Point</Label>
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
                                rushing_points_per_yard: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                      <div className="col-span-2">
                        <Label>TD Points</Label>
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
                                rushing_td_points: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                    </div>
                  </div>

                  <div className="mt-4">
                    <h3 className="text-lg font-medium">Miscellaneous</h3>
                    <Separator className="my-2" />
                    <div className="mt-2 grid grid-cols-3 gap-1">
                      <div className="col-span-2">
                        <Label>Fumble Lost Points</Label>
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
                                fumble_lost_points: parseFloat(e.target.value),
                              }
                            }))
                          }
                        />
                      </div>
                      <div className="col-span-2">
                        <Label>2-Point Conversion</Label>
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
                                two_point_conversion_points: parseFloat(e.target.value),
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
          <div className="mt-6 flex justify-end">
            <Button type="submit" disabled={processing} className="px-6">
              {processing ? 'Creating...' : 'Create League'}
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}
