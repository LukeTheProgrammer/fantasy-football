import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type CredentialFieldsProps } from '@/modules/leagues/components/credentials/credentials';
import { type CbsCredentials } from '@/types/models';

/**
 * CBS authenticates with a single cookie token rather than ESPN's pair, so the
 * two platforms share no fields beyond the league id.
 */
export function CbsCredentialFields({ credentials, onChange, errors }: CredentialFieldsProps<CbsCredentials>) {
  const set = (field: keyof CbsCredentials, value: string) => onChange({ ...credentials, [field]: value });

  return (
    <div className="mt-6 space-y-4">
      <div>
        <Label htmlFor="cbs-league-id">CBS League ID</Label>
        <div className="mt-2">
          <Input id="cbs-league-id" value={credentials.leagueId} onChange={(e) => set('leagueId', e.target.value)} className="mt-1" />
          {errors['credentials.leagueId'] && <p className="mt-1 text-sm text-red-500">{errors['credentials.leagueId']}</p>}
        </div>
      </div>

      <div>
        <Label htmlFor="cbs-token">CBS Cookie Token</Label>
        <div className="mt-2">
          <Textarea id="cbs-token" value={credentials.token} onChange={(e) => set('token', e.target.value)} className="mt-1" />
          {errors['credentials.token'] && <p className="mt-1 text-sm text-red-500">{errors['credentials.token']}</p>}
        </div>
      </div>
    </div>
  );
}
