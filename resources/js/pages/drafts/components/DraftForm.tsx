import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { type Draft, type League } from '@/types/models';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

interface DraftFormProps {
  initialData?: Partial<Draft>;
  submitEndpoint: string;
  submitMethod: 'post' | 'patch';
  submitButtonText: string;
  processingButtonText: string;
  successMessage: string;
  redirectPath: string;
  onSuccess?: (data: any) => void;
}

export function DraftForm({
  initialData,
  submitEndpoint,
  submitMethod,
  submitButtonText,
  processingButtonText,
  successMessage,
  redirectPath,
  onSuccess,
}: DraftFormProps) {
  const [data, setData] = useState<Partial<Draft>>(
    initialData || {
      id: 0,
      league_id: 0,
      draft_date: '',
      draft_type: 'snake',
      is_completed: false,
      auction_budget: 200,
      current_pick: 0,
      current_round: 0,
      time_per_pick: 60,
      is_active: false,
      league: undefined,
    },
  );
  const [leagues, setLeagues] = useState<League[]>([]);
  const [loadingLeagues, setLoadingLeagues] = useState(false);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [processing, setProcessing] = useState(false);

  // Fetch leagues for the dropdown
  useEffect(() => {
    const fetchLeagues = async () => {
      setLoadingLeagues(true);
      try {
        const response = await axios.get('/api/leagues');
        setLeagues(response.data || []);
      } catch (error) {
        console.error('Error fetching leagues:', error);
        toast.error('Failed to load leagues');
      } finally {
        setLoadingLeagues(false);
      }
    };

    fetchLeagues();
  }, []);

  // Handle form submission
  const handleSubmit = useCallback(
    async (e: React.FormEvent) => {
      e.preventDefault();
      setProcessing(true);
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
    },
    [data, submitMethod, submitEndpoint, successMessage, redirectPath, onSuccess],
  );

  // Handle input changes
  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target;

    // Handle checkbox inputs
    if (type === 'checkbox') {
      const checked = (e.target as HTMLInputElement).checked;
      setData({ ...data, [name]: checked });
      return;
    }

    // Handle number inputs
    if (type === 'number') {
      setData({ ...data, [name]: parseInt(value, 10) });
      return;
    }

    // Handle other inputs
    setData({ ...data, [name]: value });
  };

  // Handle select changes
  const handleSelectChange = (name: string, value: string) => {
    setData({ ...data, [name]: value });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
        <Card>
          <CardContent>
            <div className="mb-6">
              <h2 className="text-lg font-medium">Draft Details</h2>
            </div>

            <div className="space-y-4">
              <div>
                <Label htmlFor="league_id">League</Label>
                <Select
                  value={data.league_id ? data.league_id.toString() : ''}
                  onValueChange={(value) => {
                    const leagueId = parseInt(value, 10);
                    setData({
                      ...data,
                      league_id: leagueId,
                    });
                  }}
                  disabled={loadingLeagues}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select a league" />
                  </SelectTrigger>
                  <SelectContent>
                    {loadingLeagues ? (
                      <div className="flex items-center justify-center p-2">
                        <Skeleton className="h-5 w-full" />
                      </div>
                    ) : (
                      leagues.map((league) => (
                        <SelectItem key={league.id} value={league.id.toString()}>
                          {league.name}
                        </SelectItem>
                      ))
                    )}
                  </SelectContent>
                </Select>
                {validationErrors.league_id && <p className="mt-1 text-sm text-red-500">{validationErrors.league_id}</p>}
              </div>

              <div>
                <Label htmlFor="draft_type">Draft Type</Label>
                <Select value={data.draft_type} onValueChange={(value) => handleSelectChange('draft_type', value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select draft type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="snake">Snake</SelectItem>
                    <SelectItem value="auction">Auction</SelectItem>
                    <SelectItem value="linear">Linear</SelectItem>
                  </SelectContent>
                </Select>
                {validationErrors.draft_type && <p className="mt-1 text-sm text-red-500">{validationErrors.draft_type}</p>}
              </div>

              <div>
                <Label htmlFor="draft_date">Draft Date & Time</Label>
                <Input id="draft_date" name="draft_date" type="datetime-local" value={data.draft_date} onChange={handleChange} className="w-full" />
                {validationErrors.draft_date && <p className="mt-1 text-sm text-red-500">{validationErrors.draft_date}</p>}
              </div>

              <div>
                <Label htmlFor="time_per_pick">Time Per Pick (seconds)</Label>
                <Input
                  id="time_per_pick"
                  name="time_per_pick"
                  type="number"
                  min="0"
                  value={data.time_per_pick}
                  onChange={handleChange}
                  className="w-full"
                />
                {validationErrors.time_per_pick && <p className="mt-1 text-sm text-red-500">{validationErrors.time_per_pick}</p>}
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
