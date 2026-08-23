<?php

namespace App\Enums;

/**
 * A single FantasyPros page: one position, under one scoring format.
 *
 * FantasyPros splits the same data across a page per scoring format, so the
 * scoring format is a property of the page rather than of the rows inside it.
 * Everything that used to be inferred from a label string ('half-rb', '2-qb')
 * is declared here instead.
 */
enum FantasyProsSlate: string
{
    case SUPERFLEX = '2-qb';
    case QB = 'qb';
    case STD_RB = 'std-rb';
    case STD_WR = 'std-wr';
    case STD_TE = 'std-te';
    case STD_K = 'std-k';
    case STD_DST = 'std-dst';
    case HALF_RB = 'half-rb';
    case HALF_WR = 'half-wr';
    case HALF_TE = 'half-te';
    case PPR_RB = 'ppr-rb';
    case PPR_WR = 'ppr-wr';
    case PPR_TE = 'ppr-te';

    /**
     * The position this slate covers.
     */
    public function position(): NFLPositions
    {
        return match ($this) {
            self::SUPERFLEX, self::QB                 => NFLPositions::QB,
            self::STD_RB, self::HALF_RB, self::PPR_RB => NFLPositions::RB,
            self::STD_WR, self::HALF_WR, self::PPR_WR => NFLPositions::WR,
            self::STD_TE, self::HALF_TE, self::PPR_TE => NFLPositions::TE,
            self::STD_K                               => NFLPositions::K,
            self::STD_DST                             => NFLPositions::DST,
        };
    }

    /**
     * Points per reception this slate is scored under.
     */
    public function ppr(): float
    {
        return match ($this) {
            self::HALF_RB, self::HALF_WR, self::HALF_TE               => 0.5,
            self::PPR_RB, self::PPR_WR, self::PPR_TE, self::SUPERFLEX => 1.0,
            default                                                   => 0.0,
        };
    }

    /**
     * Whether the slate is scored for a superflex or two quarterback league.
     */
    public function isSuperflex(): bool
    {
        return $this === self::SUPERFLEX;
    }

    /**
     * The page slug FantasyPros serves this slate under.
     */
    public function slug(): string
    {
        return match ($this) {
            self::SUPERFLEX => 'superflex',
            self::QB        => 'qb',
            self::STD_RB    => 'rb',
            self::STD_WR    => 'wr',
            self::STD_TE    => 'te',
            self::STD_K     => 'k',
            self::STD_DST   => 'dst',
            self::HALF_RB   => 'half-point-ppr-rb',
            self::HALF_WR   => 'half-point-ppr-wr',
            self::HALF_TE   => 'half-point-ppr-te',
            self::PPR_RB    => 'ppr-rb',
            self::PPR_WR    => 'ppr-wr',
            self::PPR_TE    => 'ppr-te',
        };
    }

    /**
     * The consensus rankings page for this slate.
     */
    public function rankingsUrl(): string
    {
        return 'https://www.fantasypros.com/nfl/rankings/' . $this->slug() . '.php';
    }

    /**
     * The weekly projections page for this slate.
     *
     * Projections are published per position rather than per scoring format,
     * with the scoring format passed as a query parameter.
     */
    public function projectionsUrl(): string
    {
        $position = strtolower($this->position()->value);

        $scoring = match ($this->ppr()) {
            0.5     => 'HALF',
            1.0     => 'PPR',
            default => 'STD',
        };

        return 'https://www.fantasypros.com/nfl/projections/' . $position . '.php?scoring=' . $scoring;
    }

    /**
     * Slates keyed by value, for command prompts.
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
