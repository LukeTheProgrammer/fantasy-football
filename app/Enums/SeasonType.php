<?php

namespace App\Enums;

/**
 * Which part of a season a game or stat line belongs to.
 *
 * Sources spell the postseason several ways — nflverse marks the round (WC,
 * DIV, CON, SB) rather than a single value — so anything that is not the
 * regular season is folded into one case here.
 */
enum SeasonType: string
{
    case REGULAR = 'regular';
    case POST = 'post';

    /**
     * Labels the postseason is spelled with, by round.
     *
     * @var array<int, string>
     */
    public const POST_LABELS = ['POST', 'WC', 'DIV', 'CON', 'SB'];

    /**
     * The case a source's own label maps to.
     */
    public static function fromSource(?string $value): self
    {
        return in_array(strtoupper(trim((string) $value)), self::POST_LABELS, true)
            ? self::POST
            : self::REGULAR;
    }
}
