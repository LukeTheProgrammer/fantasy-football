<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Action;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Point the throwaway mock league at the mock draft room that is open now.
 *
 * ESPN mints a fresh league id for every mock draft, and the frame tap resolves
 * a sale to a board by that id alone (see ProcessDraftFramesJob), so a board
 * built for yesterday's mock is deaf to today's. Rather than seed a new league
 * per mock -- each one a new draft id, so a new room url to go and find -- one
 * league is kept and repointed. The room url never changes.
 *
 *     php artisan mock:draft 360329220
 *
 * The teams, scoring and roster are copied from a real league so the board
 * prices players the way it will on draft night. Team ids 1..N are what an
 * ESPN mock hands out, which is what a SOLD frame names a team by.
 */
class MockDraft extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mock:draft
        { league_id      : ESPN league id, the leagueId in the mock draft room url }
        { --from=        : League to copy scoring, roster and team names from (default: the newest one) }
        { --budget=200   : Auction budget per team }
        { --keep-picks   : Leave the picks already on the mock board alone }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Point the mock draft board at an ESPN mock draft room.';

    /**
     * The slug is how the mock league is found again, so it is fixed.
     */
    protected const SLUG = 'espn-mock-draft';

    public function handle(): int
    {
        $platformId = (string) $this->argument('league_id');

        $source = $this->sourceLeague();

        if (!$source instanceof League) {
            $this->error('No league to copy from. Import one first, or pass --from.');

            return self::FAILURE;
        }

        $league = League::where('slug', self::SLUG)->first();

        $league = $league instanceof League
            ? $this->repoint($league, $source, $platformId)
            : $this->create($source, $platformId);

        $draft = $league->draft;

        $this->clearPicks($draft);

        $this->table(['', ''], [
            ['ESPN league', $platformId],
            ['Copied from', $source->name . ' ' . $source->season_id],
            ['League', $league->id],
            ['Draft', $draft->id],
            ['Teams', LeagueMember::forLeague($league)->count()],
            ['Budget', $draft->auction_budget],
            ['Picks', DraftPick::where('draft_id', $draft->id)->count()],
        ]);

        $this->info(route('drafts.draft-room', $draft));

        return self::SUCCESS;
    }

    /**
     * The league the mock is modelled on. Its newest season is the one the
     * mock is drafting against, so that is the row read.
     */
    protected function sourceLeague(): ?League
    {
        $query = League::with('settings', 'members')
            ->where('slug', '!=', self::SLUG)
            ->orderByDesc('season_id');

        if ($this->option('from')) {
            $query->where('id', $this->option('from'));
        }

        return $query->first();
    }

    /**
     * An existing mock league, aimed at a new room.
     *
     * Only the ids change. The members keep their rows, so a board left open
     * from the last mock still resolves the same teams.
     */
    protected function repoint(League $league, League $source, string $platformId): League
    {
        Action::model(League::class)->update($league, [
            'platform_id' => $platformId,
            'credentials' => $this->credentials($source, $platformId),
            'team_count'  => $source->team_count,
            'is_active'   => true,
        ]);

        Action::model(LeagueSettings::class)->update(
            $league->settings,
            $source->settings->toArray()
        );

        Action::model(Draft::class)->update($league->draft, [
            'draft_type'     => 'auction',
            'draft_date'     => now(),
            'auction_budget' => (int) $this->option('budget'),
            'is_completed'   => false,
            'is_active'      => true,
        ]);

        return $league->refresh()->load('draft');
    }

    protected function create(League $source, string $platformId): League
    {
        $league = Action::model(League::class)->create($this->owner($source), [
            'name'        => 'ESPN Mock Draft',
            'season'      => $source->season_id,
            'description' => 'Throwaway board for testing the draft-frame tap against an ESPN mock auction.',
            'platform_id' => $platformId,
            'team_count'  => $source->team_count,
            'is_public'   => false,
            'credentials' => $this->credentials($source, $platformId),
            'settings'    => $source->settings->toArray(),
            'members'     => $this->members($source),
            'draft'       => [
                'draft_type'     => 'auction',
                'draft_date'     => now(),
                'auction_budget' => (int) $this->option('budget'),
                'is_active'      => true,
            ],
        ]);

        // The slug the action derives from the name is what the league is found
        // by on the next run, so it is pinned rather than left to the name.
        $league->forceFill(['slug' => self::SLUG])->save();

        return $league->load('draft');
    }

    /**
     * The teams, in the order ESPN numbers them.
     *
     * A mock hands out team ids 1..N and seats you at the one in the room url,
     * so the source league's own external ids are replaced by position. The
     * names come along only so the board reads like the real thing.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function members(League $source): array
    {
        return $source->members
            ->sortBy(fn (LeagueMember $member) => (int) $member->external_id)
            ->values()
            ->map(fn (LeagueMember $member, int $index) => [
                'team_name'   => $member->team_name,
                'owner_name'  => $member->owner_name,
                'external_id' => (string) ($index + 1),
            ])
            ->all();
    }

    /**
     * The cookies belong to the account, not the league, so the real league's
     * pair is reused with the mock's id written over the top.
     *
     * @return array<string, mixed>
     */
    protected function credentials(League $source, string $platformId): array
    {
        return array_merge($source->credentials ?? [], ['leagueId' => (int) $platformId]);
    }

    protected function owner(League $source): User
    {
        return User::find($source->created_by_user_id) ?? User::firstOrFail();
    }

    /**
     * A mock board starts empty -- last mock's sales are not this one's.
     */
    protected function clearPicks(Draft $draft): void
    {
        if ($this->option('keep-picks')) {
            return;
        }

        DraftPick::where('draft_id', $draft->id)->delete();
    }
}
