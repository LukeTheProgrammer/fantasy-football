<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use App\Models\League;
use App\Models\LeagueMember;
use App\Services\Espn\Helpers\DraftSocketConnector;
use App\Services\Espn\Helpers\DraftSocketUrl;
use Exception;
use Illuminate\Console\Command;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;

/**
 * Connect to ESPN's live draft socket and write every frame down.
 *
 * ESPN's REST views publish nothing while a draft is running — mDraftDetail
 * returns a grid of playerId -1 placeholders and the rosters come back empty
 * — so the socket is the only live source of picks. Nothing here parses or
 * persists: the frame protocol is undocumented, and the field meanings have
 * to be read off a real auction before anything is built on top of them.
 */
class ListenDraftSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:draft:listen
        { league    : Local league ID }
        { --team=   : ESPN team ID to join as, defaults to the league member linked to a user }
        { --log=    : File to append frames to, defaults to storage/logs/espn-draft-{league}.log }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to the ESPN live draft socket and log every frame received.';

    protected League $league;

    protected int|string $teamId;

    protected string $logPath;

    /**
     * Seconds to wait before reconnecting; doubles on each failure so a draft
     * that has not started yet does not hammer ESPN.
     */
    protected int $backoff = 1;

    public function handle(): int
    {
        $this->league = League::findOrFail($this->argument('league'));

        $teamId = $this->option('team') ?? $this->defaultTeamId();

        if (!$teamId) {
            $this->error('No team to join as. Pass --team with an ESPN team id.');

            return Command::FAILURE;
        }

        $this->teamId = $teamId;

        $this->logPath = $this->option('log')
            ?? storage_path('logs/espn-draft-' . $this->league->id . '.log');

        $this->info('Joining league ' . $this->league->credentials['leagueId'] . ' as team ' . $this->teamId);
        $this->info('Logging frames to ' . $this->logPath);

        $this->connect();

        Loop::get()->run();

        return Command::SUCCESS;
    }

    /**
     * The ESPN team id of whichever member is tied to a local user.
     */
    protected function defaultTeamId(): int|string|null
    {
        $member = $this->league->members()
            ->whereNotNull('user_id')
            ->first();

        return $member instanceof LeagueMember ? $member->external_id : null;
    }

    protected function connect(): void
    {
        try {
            $url = $this->socketUrl();

        } catch (Exception $e) {
            $this->record('ERROR minting draft security token: ' . $e->getMessage());

            $this->reconnect();

            return;
        }

        $connector = new DraftSocketConnector(Loop::get());

        // The handshake carries no cookies at all — the token in the url is the
        // whole of the auth — but ESPN's load balancer answers 403 to a request
        // that does not look like a browser.
        $headers = [
            'Origin'          => DraftSocketUrl::ORIGIN,
            'User-Agent'      => DraftSocketUrl::USER_AGENT,
            'Pragma'          => 'no-cache',
            'Cache-Control'   => 'no-cache',
            'Accept-Language' => 'en-US,en;q=0.9',
        ];

        $connector($url, [], $headers)->then(
            function (WebSocket $conn) {
                $this->backoff = 1;

                $this->record('OPEN');

                $conn->on('message', fn (MessageInterface $msg) => $this->record((string) $msg));

                $conn->on('close', function ($code = null, $reason = null) {
                    $this->record('CLOSE ' . $code . ' ' . $reason);

                    $this->reconnect();
                });
            },
            function (Exception $e) {
                $this->record('ERROR ' . $e->getMessage());

                $this->reconnect();
            }
        );
    }

    /**
     * The token is good for one session, so a reconnect mints a fresh one.
     */
    protected function socketUrl(): string
    {
        $credentials = $this->league->credentials;

        $draftSecurity = Espn::getFantasyDraftSecurity(
            $credentials,
            $this->teamId,
            $this->league->season
        );

        return DraftSocketUrl::build(
            $credentials['leagueId'],
            $this->teamId,
            $credentials['swid'],
            $draftSecurity
        );
    }

    protected function reconnect(): void
    {
        $wait = $this->backoff;

        $this->backoff = min($this->backoff * 2, 60);

        $this->record('RECONNECT in ' . $wait . 's');

        Loop::get()->addTimer($wait, fn () => $this->connect());
    }

    /**
     * Frames go to the terminal and the log file both, timestamped, exactly as
     * they arrived — decoding them is the next job, not this one.
     */
    protected function record(string $frame): void
    {
        $line = now()->toDateTimeString() . ' ' . $frame;

        $this->line($line);

        file_put_contents($this->logPath, $line . PHP_EOL, FILE_APPEND);
    }
}
