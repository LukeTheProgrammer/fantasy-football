<?php

namespace App\Services\Espn\Helpers;

use Psr\Http\Message\RequestInterface;
use Ratchet\Client\Connector;

/**
 * Pawl, connecting to ESPN's draft socket without mangling the JOIN url.
 *
 * ESPN carries the whole session in the query string, SWID braces and token
 * colons unencoded. Guzzle's URI normalisation percent-encodes all three, and
 * ESPN then fails to match the connection to a member: the socket opens and
 * immediately answers `ERROR 1 No team with ID 1 found in league ...`.
 *
 * Guzzle writes the request line from the request target rather than the URI,
 * so overriding that with the raw path and query puts the url back on the wire
 * exactly as the browser's draft room sends it.
 */
class DraftSocketConnector extends Connector
{
    protected function generateRequest($url, array $subProtocols, array $headers): RequestInterface
    {
        $request = parent::generateRequest($url, $subProtocols, $headers);

        $target = (string) substr($url, strpos($url, '://') + 3);
        $target = (string) substr($target, strpos($target, '/'));

        return $request->withRequestTarget($target);
    }
}
