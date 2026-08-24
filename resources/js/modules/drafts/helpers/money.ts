/**
 * Auction dollars, with an em dash where a value could not be estimated.
 */
export function money(value: number | null | undefined): string {
  return value === null || value === undefined ? '—' : `$${value}`;
}
