import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useState } from 'react';
import { toast } from 'sonner';

interface LeagueFormData {
  platform: string;
  espn_league_id: string | number;
  espn_s2: string;
  espn_swid: string;
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
  platform: 'espn',
  espn_league_id: '',
  espn_s2:
    'AECFlzzeJ8XDumpNnipkrOUpZKHLnOYHmY%2BgRgwol3DvlUBavY%2BFumaCzcBxUQ%2Brg1h9HJRWWI%2FoY1qs%2BcqohAJ%2FzozkV5QIs6AHhUwhfrCOk4vzIQlrLNQIeN1N6T0LPpOw4hZmnRhpRy21%2F9xMn6dSozcElj28tJwZCj8zajnhLDgXjJ92ei6R5BEvPVKXpt%2F0azG8EpOmDPeq%2BSWxaZg74rnQJ8PfmUkVmz3c4k%2FXw4RHbNC3cslndrGNflUxkgq20blEIEpKqbAwCztCKnDRyuKt0b5pVYkXJFPJnkl5wg%3D%3D',
  espn_swid: '{D5956E6B-2C41-428B-9E26-67AC379841B0}',
};

export function LeagueForm({
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

  // Handle form submission
  const handleSubmit = useCallback(
    async (e: React.FormEvent) => {
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
    },
    [data, submitMethod, submitEndpoint, successMessage, redirectPath, onSuccess],
  );

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-4 gap-6">
        <Card className="col-span-2 col-start-2">
          <CardContent>
            <div className="mb-6">
              <h2 className="text-lg font-medium">League Details</h2>
              <p className="text-sm text-muted-foreground">Basic information about your league.</p>
            </div>

            <div className="space-y-4">
              <div>
                <Label htmlFor="platform" className="pb-4">
                  League Platform
                </Label>
                <div className="mt-2">
                  <Select value={data.platform} onValueChange={(value) => setData((prev) => ({ ...prev, platform: value }))}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select a platform" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="espn">ESPN</SelectItem>
                      <SelectItem value="cbs" disabled>
                        CBS
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  {(errors.platform || validationErrors.platform) && (
                    <p className="mt-1 text-sm text-red-500">{errors.platform || validationErrors.platform}</p>
                  )}
                </div>
                <Separator />
              </div>

              {data.platform === 'espn' && (
                <div className="mt-6 space-y-4">
                  <div>
                    <Label htmlFor="espn-league-id">ESPN League ID</Label>
                    <div className="mt-2">
                      <Input
                        id="espn-league-id"
                        value={data.espn_league_id}
                        onChange={(e) => setData((prev) => ({ ...prev, espn_league_id: e.target.value }))}
                        className="mt-1"
                      />
                      {(errors.espn_league_id || validationErrors.espn_league_id) && (
                        <p className="mt-1 text-sm text-red-500">{errors.espn_league_id || validationErrors.espn_league_id}</p>
                      )}
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="espn-swid">ESPN SWID Cookie</Label>
                    <div className="mt-2">
                      <Input
                        id="espn-swid"
                        value={data.espn_swid}
                        onChange={(e) => setData((prev) => ({ ...prev, espn_swid: e.target.value }))}
                        className="mt-1"
                      />
                      {(errors.espn_swid || validationErrors.espn_swid) && (
                        <p className="mt-1 text-sm text-red-500">{errors.espn_swid || validationErrors.espn_swid}</p>
                      )}
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="espn-s2">ESPN S2 Cookie</Label>
                    <div className="mt-2">
                      <Textarea
                        id="espn-s2"
                        value={data.espn_s2}
                        onChange={(e) => setData((prev) => ({ ...prev, espn_s2: e.target.value }))}
                        className="mt-1"
                      />
                      {(errors.espn_s2 || validationErrors.espn_s2) && (
                        <p className="mt-1 text-sm text-red-500">{errors.espn_s2 || validationErrors.espn_s2}</p>
                      )}
                    </div>
                  </div>
                </div>
              )}
            </div>
          </CardContent>
          <CardFooter>
            <div className="flex w-full justify-end space-x-2 pt-8 align-bottom">
              <Button type="submit" disabled={processing}>
                {processing ? processingButtonText : submitButtonText}
              </Button>
            </div>
          </CardFooter>
        </Card>
      </div>
    </form>
  );
}
