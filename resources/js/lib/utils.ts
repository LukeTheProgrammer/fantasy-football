import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { type Draft, type League } from '@/types/models';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function getDraftUserMember(draft: Draft, userId: number) {
  return draft?.league?.members?.find((member) => member.user_id === userId);
}

export function isUserDraftAdmin(draft: Draft, userId: number) {
  return getDraftUserMember(draft, userId)?.is_admin || false;
}

export function getLeagueUserMember(league: League, userId: number) {
  return league?.members?.find((member) => member.user_id === userId);
}

export function isUserLeagueAdmin(league: League, userId: number) {
  return getLeagueUserMember(league, userId)?.is_admin || false;
}

