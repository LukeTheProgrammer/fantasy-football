import { type DraftPick, type RosterSlotPlayer } from '@/types/models';

/**
 * The most expensive picks made at one position, best first.
 *
 * Snake drafts have no prices, so there the earliest picks are the top ones:
 * what a team gave up is the pick itself.
 */
export function topPicksAtPosition(picks: DraftPick[], position: string, isAuction: boolean, limit = 10): RosterSlotPlayer[] {
  return picks
    .filter((pick) => pick.player?.position_id === position)
    .sort((a, b) => (isAuction ? Number(b.amount) - Number(a.amount) : a.pick_number - b.pick_number))
    .slice(0, limit)
    .map((pick) => ({
      player_id: pick.player_id,
      pick_id: pick.id,
      full_name: pick.player.full_name,
      position_id: pick.player.position_id,
      team_id: pick.player.team_id,
      amount: Number(pick.amount),
      round: pick.round,
      pick_number: pick.pick_number,
    }));
}
