import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type CredentialFieldsProps } from '@/modules/leagues/components/credentials/credentials';
import { type EspnCredentials } from '@/types/models';

/**
 * ESPN authenticates with the two cookies a signed in browser carries, so the
 * fields are the league id and that cookie pair.
 */
export function EspnCredentialFields({ credentials, onChange, errors }: CredentialFieldsProps<EspnCredentials>) {
  const set = (field: keyof EspnCredentials, value: string) => onChange({ ...credentials, [field]: value });

  return (
    <div className="mt-6 space-y-4">
      <div>
        <Label htmlFor="espn-league-id">ESPN League ID</Label>
        <div className="mt-2">
          <Input id="espn-league-id" value={credentials.leagueId} onChange={(e) => set('leagueId', e.target.value)} className="mt-1" />
          {errors['credentials.leagueId'] && <p className="mt-1 text-sm text-red-500">{errors['credentials.leagueId']}</p>}
        </div>
      </div>

      <div>
        <Label htmlFor="espn-swid">ESPN SWID Cookie</Label>
        <div className="mt-2">
          <Input id="espn-swid" value={credentials.swid} onChange={(e) => set('swid', e.target.value)} className="mt-1" />
          {errors['credentials.swid'] && <p className="mt-1 text-sm text-red-500">{errors['credentials.swid']}</p>}
        </div>
      </div>

      <div>
        <Label htmlFor="espn-s2">ESPN S2 Cookie</Label>
        <div className="mt-2">
          <Textarea id="espn-s2" value={credentials.s2} onChange={(e) => set('s2', e.target.value)} className="mt-1" />
          {errors['credentials.s2'] && <p className="mt-1 text-sm text-red-500">{errors['credentials.s2']}</p>}
        </div>
      </div>
    </div>
  );
}
