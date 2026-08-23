<?php

namespace App\Enums;

/**
 * A FantasyPros overall draft board, one per scoring format.
 *
 * These are the cheatsheet pages, which rank every position against each other
 * and carry tiers. The positional pages behind {@see FantasyProsSlate} rank
 * within a position only, so their rank means something different and belongs
 * with projections rather than with draft rankings.
 */
enum FantasyProsDraftSlate: string
{
    case STD = 'std';
    case HALF = 'half';
    case PPR = 'ppr';
    case SUPERFLEX = 'superflex';
    case DYNASTY = 'dynasty';

    /**
     * Points per reception this board is scored under.
     */
    public function ppr(): float
    {
        return match ($this) {
            self::HALF               => 0.5,
            self::PPR, self::DYNASTY => 1.0,
            default                  => 0.0,
        };
    }

    /**
     * Whether the board is scored for a superflex or two quarterback league.
     */
    public function isSuperflex(): bool
    {
        return $this === self::SUPERFLEX;
    }

    /**
     * Whether the board ranks for a redraft or a dynasty league.
     */
    public function type(): string
    {
        return $this === self::DYNASTY ? 'dynasty' : 'redraft';
    }

    /**
     * The page slug FantasyPros serves this board under.
     */
    public function slug(): string
    {
        return match ($this) {
            self::STD       => 'consensus-cheatsheets',
            self::HALF      => 'half-point-ppr-cheatsheets',
            self::PPR       => 'ppr-cheatsheets',
            self::SUPERFLEX => 'superflex-cheatsheets',
            self::DYNASTY   => 'dynasty-overall',
        };
    }

    public function url(): string
    {
        return 'https://www.fantasypros.com/nfl/rankings/' . $this->slug() . '.php';
    }

    /**
     * Boards keyed by value, for command prompts.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $opts = [];

        foreach (self::cases() as $case) {
            $opts[$case->value] = $case->value;
        }

        return $opts;
    }
}
