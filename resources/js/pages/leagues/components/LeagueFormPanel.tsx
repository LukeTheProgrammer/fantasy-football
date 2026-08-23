import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import { useCallback, useState } from 'react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

interface LeagueFormData {
  platform: string;
  espn_league_id: string|number;
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
  espn_s2: '',
  espn_swid: '',
};

export function LeagueFormPanel({
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
      <div className="grid grid-cols-4 gap-6">
        <Card className="col-span-2 col-start-2">
          <CardContent>
            <div className="mb-6">
              <h2 className="text-lg font-medium">League Details</h2>
              <p className="text-sm text-muted-foreground">Basic information about your league.</p>
            </div>

            <div className="space-y-4">
              <div>
                <Label htmlFor="platform" className="pb-4">League Platform</Label>
                <div className="mt-2">
                  <Select
                    value={data.platform}
                    onValueChange={(value) => setData(prev => ({ ...prev, platform: value }))}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a platform" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="espn">ESPN</SelectItem>
                      <SelectItem value="cbs" disabled>CBS</SelectItem>
                    </SelectContent>
                  </Select>
                  {(errors.platform || validationErrors.platform) && (
                    <p className="text-sm text-red-500 mt-1">{errors.platform || validationErrors.platform}</p>
                  )}
                </div>
                <Separator />
              </div>

              {data.platform === 'espn' && (
                <div className="space-y-4 mt-6">
                  <div>
                    <Label htmlFor="espn-league-id">ESPN League ID</Label>
                    <div className="mt-2">
                      <Input
                        id="espn-league-id"
                        value={data.espn_league_id}
                        onChange={(e) => setData(prev => ({ ...prev, espn_league_id: e.target.value }))}
                        className="mt-1"
                      />
                      {(errors.espn_league_id || validationErrors.espn_league_id) && (
                        <p className="text-sm text-red-500 mt-1">{errors.espn_league_id || validationErrors.espn_league_id}</p>
                      )}
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="espn-swid">ESPN SWID Cookie</Label>
                    <div className="mt-2">
                      <Input
                        id="espn-swid"
                        value={data.espn_swid}
                        onChange={(e) => setData(prev => ({ ...prev, espn_swid: e.target.value }))}
                        className="mt-1"
                      />
                      {(errors.espn_swid || validationErrors.espn_swid) && (
                        <p className="text-sm text-red-500 mt-1">{errors.espn_swid || validationErrors.espn_swid}</p>
                      )}
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="espn-s2">ESPN S2 Cookie</Label>
                    <div className="mt-2">
                      <Textarea
                        id="espn-s2"
                        value={data.espn_s2}
                        onChange={(e) => setData(prev => ({ ...prev, espn_s2: e.target.value }))}
                        className="mt-1"
                      />
                      {(errors.espn_s2 || validationErrors.espn_s2) && (
                        <p className="text-sm text-red-500 mt-1">{errors.espn_s2 || validationErrors.espn_s2}</p>
                      )}
                    </div>
                  </div>
                </div>
              )}
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
