import { cn } from '@/common/helpers/cn';
import { c } from '@/common/helpers/conv';
import { TeamAvatar } from '@/modules/leagues/components/TeamAvatar';
import { type LeagueMatchupResource, type LeagueResource, type LeagueTeamResource } from '@/types/resources';
import { useMemo } from 'react';

interface PlayoffBracketProps {
  league: LeagueResource;
}

/** The bracket that decides the title. ESPN runs a consolation ladder beside it. */
const WINNERS_BRACKET = 'WINNERS_BRACKET';

interface BracketRound {
  week: number;
  matchups: LeagueMatchupResource[];
}

/**
 * The championship bracket, round by round.
 *
 * Rounds come from the weeks the platform itself marked as playoff games rather
 * than from a count of regular season weeks, so a league that shortens or moves
 * its playoffs still draws correctly. A game with one team in it is a first
 * round bye and is shown as one: the bye is what the seed was worth.
 */
export function PlayoffBracket({ league }: PlayoffBracketProps) {
  const rounds = useMemo<BracketRound[]>(() => {
    const byWeek = new Map<number, LeagueMatchupResource[]>();

    for (const matchups of Object.values(league.matchups)) {
      for (const matchup of matchups) {
        if (matchup.playoff_tier !== WINNERS_BRACKET) {
          continue;
        }

        byWeek.set(matchup.week, [...(byWeek.get(matchup.week) ?? []), matchup]);
      }
    }

    const weeks = [...byWeek.entries()].sort(([a], [b]) => a - b);

    // Every round is laid out against the first round's shape, so a semi final
    // sits under the games that fed it.
    const positions = seedPositions((weeks[0]?.[1].length ?? 0) * 2);

    return weeks.map(([week, matchups]) => ({
      week,
      matchups: [...matchups].sort((a, b) => bracketPosition(a, positions) - bracketPosition(b, positions)),
    }));
  }, [league.matchups]);

  if (rounds.length === 0) {
    return null;
  }

  return (
    <div className="mb-8 w-full rounded-lg border bg-card p-4">
      <div className="mb-6 flex w-full items-center justify-start">
        <h4 className="text-lg font-semibold">Playoff Bracket</h4>
      </div>

      <div className={`grid w-full gap-6 grid-cols-${rounds.length} mb-4`}>
        {rounds.map((round, index) => (
          <p className="text-center font-medium text-muted-foreground uppercase">
            {roundName(index, rounds.length)} · Week {round.week}
          </p>
        ))}
      </div>

      <div className="flex gap-6 overflow-x-auto pb-2">
        {rounds.map((round) => (
          <div key={round.week} className="flex min-w-[16rem] flex-1 flex-col justify-around gap-4">
            {round.matchups.map((matchup) => (
              <BracketMatchup key={matchup.id} matchup={matchup} />
            ))}
          </div>
        ))}
      </div>
    </div>
  );
}

/**
 * Where each seed sits in a bracket of this size, top to bottom.
 *
 * Seeds are paired best against worst and then the bottom half is mirrored, so
 * the top two seeds sit at the extremes and a bye is drawn beside the seed that
 * earned it: 1, 4v5, 3v6, 2 rather than 4v5, 3v6, 1, 2.
 */
function seedPositions(teams: number): Map<number, number> {
  let size = 1;

  while (size < teams) {
    size *= 2;
  }

  let order = [1];

  while (order.length < size) {
    const round = order.length * 2;

    order = order.flatMap((seed) => [seed, round + 1 - seed]);
  }

  const pairs: number[][] = [];

  for (let index = 0; index < order.length; index += 2) {
    pairs.push(order.slice(index, index + 2));
  }

  const mirrored = pairs.length < 2 ? pairs : [...pairs.slice(0, pairs.length / 2), ...pairs.slice(pairs.length / 2).reverse()];

  return new Map(mirrored.flat().map((seed, index) => [seed, index]));
}

/**
 * A game sits where its better seed does. A game with no seeds on it at all —
 * a season the platform never settled — falls to the bottom rather than
 * jumping the bracket.
 */
function bracketPosition(matchup: LeagueMatchupResource, positions: Map<number, number>): number {
  const seeds = [matchup.home_team.playoff_seed, matchup.away_team?.playoff_seed ?? null].filter((seed): seed is number => seed !== null);

  const places = seeds.map((seed) => positions.get(seed) ?? Number.MAX_SAFE_INTEGER);

  return places.length === 0 ? Number.MAX_SAFE_INTEGER : Math.min(...places);
}

/**
 * The last round is the final whatever the bracket's size; the ones before it
 * are named by how many teams are still in.
 */
function roundName(index: number, total: number): string {
  const fromEnd = total - index;

  if (fromEnd === 1) {
    return 'Championship';
  }

  if (fromEnd === 2) {
    return 'Semifinals';
  }

  if (fromEnd === 3) {
    return 'Quarterfinals';
  }

  return `Round ${index + 1}`;
}

function BracketMatchup({ matchup }: { matchup: LeagueMatchupResource }) {
  // A bye is the seed's reward, so it is drawn as the team advancing alone.
  if (!matchup.away_team) {
    return (
      <div className="rounded-md border">
        <BracketTeam team={matchup.home_team} score={matchup.home_score} won={true} />
        <div className="border-t px-3 py-4 text-xs text-muted-foreground">Bye</div>
      </div>
    );
  }

  const homeScore = c(matchup.home_score).toFloat();
  const awayScore = c(matchup.away_score).toFloat();

  // The platform's own call settles a tie; scores decide it only when the game
  // is played out and it never recorded one.
  const decided = matchup.winner === 'HOME' || matchup.winner === 'AWAY';
  const played = homeScore > 0 || awayScore > 0;

  const homeWon = decided ? matchup.winner === 'HOME' : played && homeScore > awayScore;
  const awayWon = decided ? matchup.winner === 'AWAY' : played && awayScore > homeScore;

  return (
    <div className="rounded-md border">
      <BracketTeam team={matchup.home_team} score={matchup.home_score} won={homeWon} />
      <div className="border-t">
        <BracketTeam team={matchup.away_team} score={matchup.away_score} won={awayWon} />
      </div>
    </div>
  );
}

function BracketTeam({ team, score, won }: { team: LeagueTeamResource; score: number; won: boolean }) {
  return (
    <div className={cn('flex items-center gap-2 px-3 py-2', !won && 'text-muted-foreground')}>
      {team.playoff_seed !== null && <span className="w-4 shrink-0 text-xs text-muted-foreground tabular-nums">{team.playoff_seed}</span>}
      <TeamAvatar member={team} />
      <span className={cn('min-w-0 flex-1 truncate text-sm', won && 'font-semibold')}>{team.team_name}</span>
      <span className="shrink-0 text-sm tabular-nums">{c(score).toFloat() || '—'}</span>
    </div>
  );
}
