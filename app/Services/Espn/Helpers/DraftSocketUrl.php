<?php

namespace App\Services\Espn\Helpers;

/**
 * The JOIN url for ESPN's live draft socket.
 *
 * ESPN carries the whole session in numbered query parameters rather than
 * headers or a body, so the url is the entire handshake:
 *
 *   1 game id (1 = ffl)      5 session token, see below
 *   2 league id              6 unknown flag, the room sends false
 *   3 team id                7 unknown flag, the room sends false
 *   4 SWID, braces included  8 client name, the room sends KONA
 *
 * Parameter 5 is `{game}:{league}:{team}:{SWID}:{draftSecurity}`, where the
 * last part comes from GetDraftSecurity and is good for one session.
 */
class DraftSocketUrl
{
    public const HOST = 'wss://fantasydraft.espn.com';

    /**
     * The origin ESPN's own draft room sends; the handshake wants it.
     */
    public const ORIGIN = 'https://fantasy.espn.com';

    /**
     * ESPN's load balancer answers 403 to a handshake that does not look like a
     * browser, so the draft room's own user agent is sent.
     */
    public const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36';

    public const GAME_ID = 1;

    public const CLIENT = 'KONA';

    public static function build(
        int|string $leagueId,
        int|string $teamId,
        string $swid,
        int|string $draftSecurity,
    ): string {
        $token = implode(':', [self::GAME_ID, $leagueId, $teamId, $swid, $draftSecurity]);

        // ESPN's room sends the braces of the SWID and the colons of the
        // token unencoded, and the server rejects the percent-encoded forms,
        // so the query is assembled by hand rather than by http_build_query().
        $query = implode('&', [
            '1=' . self::GAME_ID,
            '2=' . $leagueId,
            '3=' . $teamId,
            '4=' . $swid,
            '5=' . $token,
            '6=false',
            '7=false',
            '8=' . self::CLIENT,
            'nocache=' . random_int(100000, 999999),
        ]);

        return implode('/', [
            self::HOST,
            'game-' . self::GAME_ID,
            'league-' . $leagueId,
            'JOIN?' . $query,
        ]);
    }
}
