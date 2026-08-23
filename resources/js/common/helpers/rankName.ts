export function rankName(rank: number) {
  if (rank === 1) {
    return '1st';
  } else if (rank === 2) {
    return '2nd';
  } else if (rank === 3) {
    return '3rd';
  } else {
    return `${rank}th`;
  }
}
