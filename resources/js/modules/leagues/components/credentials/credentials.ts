import { type CbsCredentials, type EspnCredentials, type League } from '@/types/models';

export type Platform = 'espn' | 'cbs';

export const PLATFORMS: { value: Platform; label: string }[] = [
  { value: 'espn', label: 'ESPN' },
  { value: 'cbs', label: 'CBS' },
];

export type CredentialsFor<P extends Platform> = P extends 'espn' ? EspnCredentials : CbsCredentials;

export interface CredentialFieldsProps<C> {
  credentials: C;
  onChange: (credentials: C) => void;
  errors: Record<string, string>;
}

const emptyEspn: EspnCredentials = { leagueId: '', s2: '', swid: '' };

const emptyCbs: CbsCredentials = { leagueId: '', token: '' };

/**
 * A blank credential object for a platform, so a form always has every field
 * present and never flips an input between uncontrolled and controlled.
 */
export function emptyCredentials(platform: Platform): EspnCredentials | CbsCredentials {
  return platform === 'espn' ? { ...emptyEspn } : { ...emptyCbs };
}

/**
 * The credentials a league was imported with, filled out to the full shape for
 * its platform. A league on another platform contributes nothing, since the
 * fields do not carry over.
 */
export function credentialsFromLeague(platform: Platform, league?: League): EspnCredentials | CbsCredentials {
  const blank = emptyCredentials(platform);

  if (!league?.credentials || platformOf(league) !== platform) {
    return blank;
  }

  return { ...blank, ...league.credentials };
}

export function platformOf(league?: League): Platform {
  return league?.platform?.toLowerCase() === 'cbs' ? 'cbs' : 'espn';
}
