import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { CbsCredentialFields } from '@/modules/leagues/components/credentials/CbsCredentialFields';
import { type Platform, PLATFORMS, credentialsFromLeague, platformOf } from '@/modules/leagues/components/credentials/credentials';
import { EspnCredentialFields } from '@/modules/leagues/components/credentials/EspnCredentialFields';
import { type CbsCredentials, type EspnCredentials, type League } from '@/types/models';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useState } from 'react';
import { toast } from 'sonner';

interface LeagueFormProps {
  /** The league being edited. Absent when creating one. */
  league?: League;
  submitEndpoint: string;
  submitMethod: 'post' | 'patch';
  submitButtonText: string;
  processingButtonText: string;
  successMessage: string;
  redirectPath: string;
  onSuccess?: (data: League) => void;
}

export function LeagueForm({
  league,
  submitEndpoint,
  submitMethod,
  submitButtonText,
  processingButtonText,
  successMessage,
  redirectPath,
  onSuccess,
}: LeagueFormProps) {
  const [platform, setPlatform] = useState<Platform>(platformOf(league));
  const [credentials, setCredentials] = useState(() => credentialsFromLeague(platformOf(league), league));
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [processing, setProcessing] = useState(false);

  // Credentials do not carry between platforms, so switching starts the new
  // platform's fields blank rather than half filled with the old one's.
  const changePlatform = useCallback(
    (value: string) => {
      const next = value as Platform;

      setPlatform(next);
      setCredentials(credentialsFromLeague(next, league));
    },
    [league],
  );

  const handleSubmit = useCallback(
    async (e: React.FormEvent) => {
      e.preventDefault();
      setProcessing(true);
      setErrors({});

      try {
        const response = await axios({
          method: submitMethod,
          url: submitEndpoint,
          data: { platform, credentials },
        });

        toast.success(successMessage);

        if (onSuccess) {
          onSuccess(response.data);
        } else {
          router.visit(redirectPath);
        }
      } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
          setErrors(error.response.data.errors || {});
          toast.error('Please fix the validation errors.');
        } else {
          toast.error('An error occurred. Please try again.');
        }
      } finally {
        setProcessing(false);
      }
    },
    [platform, credentials, submitMethod, submitEndpoint, successMessage, redirectPath, onSuccess],
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
                  <Select value={platform} onValueChange={changePlatform}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select a platform" />
                    </SelectTrigger>
                    <SelectContent>
                      {PLATFORMS.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                          {option.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.platform && <p className="mt-1 text-sm text-red-500">{errors.platform}</p>}
                </div>
                <Separator />
              </div>

              {platform === 'espn' ? (
                <EspnCredentialFields credentials={credentials as EspnCredentials} onChange={setCredentials} errors={errors} />
              ) : (
                <CbsCredentialFields credentials={credentials as CbsCredentials} onChange={setCredentials} errors={errors} />
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
