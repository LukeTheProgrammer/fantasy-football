import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/use-toast';

export default function CreateLeague({ auth }: PageProps) {
  const { toast } = useToast();
  const [activeTab, setActiveTab] = useState('general');
  
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    description: '',
    max_teams: 10,
    is_public: false,
    draft_type: 'snake',
    draft_date: '',
    settings: {
      roster_positions: ['QB', 'RB', 'RB', 'WR', 'WR', 'TE', 'FLEX', 'K', 'DEF'],
      roster_size: 16,
      starters_count: 9,
      bench_count: 7,
      ir_spots: 1,
      passing_yards_per_point: 0.04,
      passing_td_points: 4,
      interception_points: -2,
      rushing_yards_per_point: 0.1,
      rushing_td_points: 6,
      receiving_yards_per_point: 0.1,
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
        '35+': -4
      }
    }
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    
    post(route('leagues.store'), {
      onSuccess: () => {
        reset();
        toast({
          title: "League Created",
          description: "Your fantasy league has been created successfully!",
        });
      },
      onError: () => {
        toast({
          title: "Error",
          description: "There was a problem creating your league. Please check the form and try again.",
          variant: "destructive"
        });
      }
    });
  }

  return (
    <>
      <Head title="Create League" />
      
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6">
              <h1 className="text-2xl font-semibold mb-6">Create a New Fantasy League</h1>
              
              <form onSubmit={handleSubmit}>
                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                  <TabsList className="grid w-full grid-cols-3">
                    <TabsTrigger value="general">General Settings</TabsTrigger>
                    <TabsTrigger value="roster">Roster Settings</TabsTrigger>
                    <TabsTrigger value="scoring">Scoring Settings</TabsTrigger>
                  </TabsList>
                  
                  <TabsContent value="general" className="mt-6">
                    <Card>
                      <CardHeader>
                        <CardTitle>League Details</CardTitle>
                        <CardDescription>
                          Set up the basic information for your fantasy football league.
                        </CardDescription>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        <div className="grid w-full items-center gap-2">
                          <Label htmlFor="name">League Name</Label>
                          <Input 
                            id="name" 
                            value={data.name} 
                            onChange={e => setData('name', e.target.value)}
                            placeholder="Enter league name" 
                          />
                          {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label htmlFor="description">Description</Label>
                          <Textarea 
                            id="description" 
                            value={data.description || ''} 
                            onChange={e => setData('description', e.target.value)}
                            placeholder="Describe your league" 
                            rows={3}
                          />
                          {errors.description && <p className="text-sm text-red-500">{errors.description}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label htmlFor="max_teams">Maximum Teams</Label>
                          <Input 
                            id="max_teams" 
                            type="number" 
                            min={2}
                            max={32}
                            value={data.max_teams} 
                            onChange={e => setData('max_teams', parseInt(e.target.value))}
                          />
                          {errors.max_teams && <p className="text-sm text-red-500">{errors.max_teams}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label htmlFor="draft_type">Draft Type</Label>
                          <Select 
                            value={data.draft_type} 
                            onValueChange={value => setData('draft_type', value)}
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Select draft type" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="snake">Snake</SelectItem>
                              <SelectItem value="auction">Auction</SelectItem>
                            </SelectContent>
                          </Select>
                          {errors.draft_type && <p className="text-sm text-red-500">{errors.draft_type}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label htmlFor="draft_date">Draft Date & Time</Label>
                          <Input 
                            id="draft_date" 
                            type="datetime-local" 
                            value={data.draft_date} 
                            onChange={e => setData('draft_date', e.target.value)}
                          />
                          {errors.draft_date && <p className="text-sm text-red-500">{errors.draft_date}</p>}
                        </div>
                        
                        <div className="flex items-center space-x-2">
                          <Switch 
                            id="is_public" 
                            checked={data.is_public} 
                            onCheckedChange={checked => setData('is_public', checked)}
                          />
                          <Label htmlFor="is_public">Make league public</Label>
                          {errors.is_public && <p className="text-sm text-red-500">{errors.is_public}</p>}
                        </div>
                      </CardContent>
                    </Card>
                  </TabsContent>
                  
                  <TabsContent value="roster" className="mt-6">
                    <Card>
                      <CardHeader>
                        <CardTitle>Roster Settings</CardTitle>
                        <CardDescription>
                          Configure your league's roster positions and size.
                        </CardDescription>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        <div className="grid w-full items-center gap-2">
                          <Label>Roster Size</Label>
                          <Input 
                            type="number" 
                            min={1}
                            value={data.settings.roster_size} 
                            onChange={e => setData('settings', {
                              ...data.settings,
                              roster_size: parseInt(e.target.value)
                            })}
                          />
                          {errors['settings.roster_size'] && <p className="text-sm text-red-500">{errors['settings.roster_size']}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label>Starting Players</Label>
                          <Input 
                            type="number" 
                            min={1}
                            value={data.settings.starters_count} 
                            onChange={e => setData('settings', {
                              ...data.settings,
                              starters_count: parseInt(e.target.value)
                            })}
                          />
                          {errors['settings.starters_count'] && <p className="text-sm text-red-500">{errors['settings.starters_count']}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label>Bench Spots</Label>
                          <Input 
                            type="number" 
                            min={0}
                            value={data.settings.bench_count} 
                            onChange={e => setData('settings', {
                              ...data.settings,
                              bench_count: parseInt(e.target.value)
                            })}
                          />
                          {errors['settings.bench_count'] && <p className="text-sm text-red-500">{errors['settings.bench_count']}</p>}
                        </div>
                        
                        <div className="grid w-full items-center gap-2">
                          <Label>IR Spots</Label>
                          <Input 
                            type="number" 
                            min={0}
                            value={data.settings.ir_spots} 
                            onChange={e => setData('settings', {
                              ...data.settings,
                              ir_spots: parseInt(e.target.value)
                            })}
                          />
                          {errors['settings.ir_spots'] && <p className="text-sm text-red-500">{errors['settings.ir_spots']}</p>}
                        </div>
                        
                        <Alert>
                          <AlertDescription>
                            Roster positions configuration will be available in a future update.
                          </AlertDescription>
                        </Alert>
                      </CardContent>
                    </Card>
                  </TabsContent>
                  
                  <TabsContent value="scoring" className="mt-6">
                    <Card>
                      <CardHeader>
                        <CardTitle>Scoring Settings</CardTitle>
                        <CardDescription>
                          Configure your league's scoring rules.
                        </CardDescription>
                      </CardHeader>
                      <CardContent>
                        <div className="space-y-6">
                          <div>
                            <h3 className="text-lg font-medium">Passing</h3>
                            <Separator className="my-2" />
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                              <div className="grid w-full items-center gap-2">
                                <Label>Yards per Point</Label>
                                <Input 
                                  type="number" 
                                  step="0.01"
                                  value={data.settings.passing_yards_per_point} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    passing_yards_per_point: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                              <div className="grid w-full items-center gap-2">
                                <Label>TD Points</Label>
                                <Input 
                                  type="number" 
                                  step="0.5"
                                  value={data.settings.passing_td_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    passing_td_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                              <div className="grid w-full items-center gap-2">
                                <Label>Interception Points</Label>
                                <Input 
                                  type="number" 
                                  step="0.5"
                                  value={data.settings.interception_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    interception_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                            </div>
                          </div>
                          
                          <div>
                            <h3 className="text-lg font-medium">Rushing</h3>
                            <Separator className="my-2" />
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                              <div className="grid w-full items-center gap-2">
                                <Label>Yards per Point</Label>
                                <Input 
                                  type="number" 
                                  step="0.01"
                                  value={data.settings.rushing_yards_per_point} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    rushing_yards_per_point: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                              <div className="grid w-full items-center gap-2">
                                <Label>TD Points</Label>
                                <Input 
                                  type="number" 
                                  step="0.5"
                                  value={data.settings.rushing_td_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    rushing_td_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                            </div>
                          </div>
                          
                          <div>
                            <h3 className="text-lg font-medium">Receiving</h3>
                            <Separator className="my-2" />
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                              <div className="grid w-full items-center gap-2">
                                <Label>Yards per Point</Label>
                                <Input 
                                  type="number" 
                                  step="0.01"
                                  value={data.settings.receiving_yards_per_point} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    receiving_yards_per_point: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                              <div className="grid w-full items-center gap-2">
                                <Label>TD Points</Label>
                                <Input 
                                  type="number" 
                                  step="0.5"
                                  value={data.settings.receiving_td_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    receiving_td_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                              <div className="grid w-full items-center gap-2">
                                <Label>Reception Points</Label>
                                <Input 
                                  type="number" 
                                  step="0.1"
                                  value={data.settings.reception_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    reception_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                            </div>
                          </div>
                          
                          <div>
                            <h3 className="text-lg font-medium">Miscellaneous</h3>
                            <Separator className="my-2" />
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                              <div className="grid w-full items-center gap-2">
                                <Label>Fumble Lost Points</Label>
                                <Input 
                                  type="number" 
                                  step="0.5"
                                  value={data.settings.fumble_lost_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    fumble_lost_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                              <div className="grid w-full items-center gap-2">
                                <Label>2-Point Conversion</Label>
                                <Input 
                                  type="number" 
                                  step="0.5"
                                  value={data.settings.two_point_conversion_points} 
                                  onChange={e => setData('settings', {
                                    ...data.settings,
                                    two_point_conversion_points: parseFloat(e.target.value)
                                  })}
                                />
                              </div>
                            </div>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  </TabsContent>
                </Tabs>
                
                <div className="mt-6 flex justify-end">
                  <Button 
                    type="submit" 
                    disabled={processing}
                    className="px-6"
                  >
                    {processing ? 'Creating...' : 'Create League'}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
