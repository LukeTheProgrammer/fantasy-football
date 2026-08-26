<?php

namespace App\Services\Player\Helpers;

use Illuminate\Support\Str;

/**
 * A player's name reduced to what every source agrees on.
 *
 * Sources disagree about punctuation and generational suffixes far more often
 * than they disagree about who someone is: FantasyPros says "Erick All Jr."
 * where the roster says "Erick All", and "A.J. Henning" and "AJ Henning" are
 * the same man. Comparing the reduced forms costs nothing and closes almost
 * every one of those gaps.
 *
 * It reduces, it does not guess — no fuzzy distance, no phonetics. Two names
 * that survive this and still differ belong to two different people until a
 * human says otherwise.
 */
class NormalizedName
{
    /**
     * Suffixes that are part of a name's presentation rather than its identity.
     *
     * @var array<int, string>
     */
    public const SUFFIXES = ['jr', 'sr', 'ii', 'iii', 'iv', 'v'];

    public static function of(?string $name): ?string
    {
        if (empty(trim((string) $name))) {
            return null;
        }

        // Accents fold to their ASCII base so "Amon-Ra" and "Amón-Ra" meet.
        $normalized = Str::ascii($name);
        $normalized = mb_strtolower($normalized);

        // A parenthetical is a roster note, not a name: "Smith (IR)".
        $normalized = preg_replace('/\s*\(.*?\)\s*/', ' ', $normalized);

        // A hyphen joins two words another source separates with a space, so it
        // becomes one; periods and apostrophes are dropped outright, which is
        // what turns "A.J." into "AJ" rather than into two words.
        $normalized = str_replace('-', ' ', $normalized);
        $normalized = preg_replace('/[.\'`\x{2019}]/u', '', $normalized);

        // Pro Bowl and All Pro markers trail the name on some season pages.
        $normalized = str_replace(['*', '+'], '', $normalized);

        $normalized = trim(preg_replace('/\s+/', ' ', $normalized));

        // The suffix comes off last, once the punctuation that hid it is gone.
        $suffixes = implode('|', self::SUFFIXES);
        $normalized = preg_replace('/\s+(' . $suffixes . ')$/', '', $normalized);

        return $normalized === '' ? null : $normalized;
    }
}
