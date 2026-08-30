<?php

namespace Tests\Feature;

use App\Events\DraftPicksSynced;
use App\Events\DraftSyncStopped;
use App\Facades\Auction as AuctionFacade;
use App\Jobs\SyncDraftPicksJob;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use App\Models\Player;
use App\Models\PlayerMissing;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DraftSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private League $league;

    private Draft $draft;

    private LeagueMember $member;

    private Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // leagues.season keys into seasons.
        Season::firstOrCreate(['id' => 2026], ['is_current' => true]);

        $this->league = League::create([
            'created_by_user_id' => $this->user->id,
            'name'               => 'Test League',
            'season_id'          => 2026,
            'platform'           => 'ESPN',
            'platform_id'        => '1',
            'team_count'         => 2,
            'slug'               => 'test-league-2026',
            'credentials'        => ['leagueId' => '1', 's2' => 's2', 'swid' => 'swid'],
        ]);

        LeagueSettings::create([
            'league_id'        => $this->league->id,
            'ppr'              => 'half-ppr',
            'two_qb'           => true,
            'roster_size'      => 3,
            'roster_positions' => ['QB', 'RB', 'BE'],
        ]);

        $this->member = LeagueMember::create([
            'league_id'   => $this->league->id,
            'user_id'     => $this->user->id,
            'external_id' => 7,
            'team_name'   => 'My Team',
            'is_admin'    => false,
        ]);

        $this->draft = Draft::create([
            'league_id'      => $this->league->id,
            'draft_type'     => 'auction',
            'auction_budget' => 200,
        ]);

        $this->player = Player::factory()->create(['espn_id' => 3139477]);
    }

    /**
     * ESPN's mDraftDetail payload, with one pick per player id given.
     */
    private function fakeEspn(array $playerIds, bool $drafted = false): void
    {
        $picks = [];

        foreach (array_values($playerIds) as $index => $playerId) {
            $picks[] = [
                'id'                => $index + 1,
                'bidAmount'         => 42,
                'keeper'            => false,
                'overallPickNumber' => $index + 1,
                'playerId'          => $playerId,
                'roundId'           => 1,
                'roundPickNumber'   => $index + 1,
                'teamId'            => 7,
            ];
        }

        Http::fake([
            'lm-api-reads.fantasy.espn.com/*' => Http::response([
                'id'          => 1,
                'seasonId'    => 2026,
                'draftDetail' => [
                    'drafted'    => $drafted,
                    'inProgress' => !$drafted,
                    'picks'      => $picks,
                ],
            ]),
        ]);
    }

    public function test_it_writes_in_picks_espn_has_that_the_board_does_not(): void
    {
        $this->fakeEspn([$this->player->espn_id]);

        $result = AuctionFacade::syncEspnPicks($this->draft);

        $this->assertCount(1, $result['created']);
        $this->assertSame(0, $result['skipped']);

        $this->assertDatabaseHas('draft_picks', [
            'draft_id'         => $this->draft->id,
            'player_id'        => $this->player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);
    }

    public function test_a_pick_already_on_the_board_is_updated_rather_than_duplicated(): void
    {
        DraftPick::create([
            'draft_id'         => $this->draft->id,
            'league_member_id' => $this->member->id,
            'player_id'        => $this->player->id,
            'round'            => 0,
            'pick_number'      => 1,
            'amount'           => 1,
        ]);

        $this->fakeEspn([$this->player->espn_id]);

        $result = AuctionFacade::syncEspnPicks($this->draft);

        $this->assertEmpty($result['created']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(1, DraftPick::where('draft_id', $this->draft->id)->count());
        $this->assertSame('42.00', DraftPick::first()->amount);
    }

    public function test_a_sale_typed_in_by_hand_survives_a_sync_that_does_not_include_it(): void
    {
        $manual = Player::factory()->create(['espn_id' => 999999]);

        DraftPick::create([
            'draft_id'         => $this->draft->id,
            'league_member_id' => $this->member->id,
            'player_id'        => $manual->id,
            'round'            => 0,
            'pick_number'      => 1,
            'amount'           => 15,
        ]);

        $this->fakeEspn([$this->player->espn_id]);

        AuctionFacade::syncEspnPicks($this->draft);

        $this->assertDatabaseHas('draft_picks', [
            'player_id' => $manual->id,
            'amount'    => 15,
        ]);
    }

    public function test_an_unresolvable_player_is_recorded_as_missing_rather_than_created(): void
    {
        $players = Player::count();

        $this->fakeEspn([424242]);

        $result = AuctionFacade::syncEspnPicks($this->draft);

        $this->assertSame(1, $result['skipped']);
        $this->assertSame($players, Player::count());
        $this->assertSame(0, DraftPick::count());
        $this->assertGreaterThan(0, PlayerMissing::count());
    }

    public function test_the_job_broadcasts_and_queues_itself_again_while_the_draft_is_active(): void
    {
        Event::fake([DraftPicksSynced::class, DraftSyncStopped::class]);
        Bus::fake([SyncDraftPicksJob::class]);

        $this->draft->update(['is_active' => true]);
        Cache::forever(SyncDraftPicksJob::tokenKey($this->draft), 'token');

        $this->fakeEspn([$this->player->espn_id]);

        (new SyncDraftPicksJob($this->draft, 'token'))->handle();

        Event::assertDispatched(DraftPicksSynced::class);
        Bus::assertDispatched(SyncDraftPicksJob::class);
    }

    public function test_the_job_stops_when_the_draft_is_no_longer_active(): void
    {
        Event::fake();
        Bus::fake([SyncDraftPicksJob::class]);

        Cache::forever(SyncDraftPicksJob::tokenKey($this->draft), 'token');

        (new SyncDraftPicksJob($this->draft, 'token'))->handle();

        Bus::assertNotDispatched(SyncDraftPicksJob::class);
        Event::assertNotDispatched(DraftPicksSynced::class);
    }

    public function test_a_stale_loop_stops_when_a_newer_one_has_started(): void
    {
        Event::fake();
        Bus::fake([SyncDraftPicksJob::class]);

        $this->draft->update(['is_active' => true]);
        Cache::forever(SyncDraftPicksJob::tokenKey($this->draft), 'newer-token');

        (new SyncDraftPicksJob($this->draft, 'older-token'))->handle();

        Bus::assertNotDispatched(SyncDraftPicksJob::class);
    }

    public function test_a_completed_draft_closes_the_loop(): void
    {
        Event::fake([DraftPicksSynced::class, DraftSyncStopped::class]);
        Bus::fake([SyncDraftPicksJob::class]);

        $this->draft->update(['is_active' => true]);
        Cache::forever(SyncDraftPicksJob::tokenKey($this->draft), 'token');

        $this->fakeEspn([$this->player->espn_id], drafted: true);

        (new SyncDraftPicksJob($this->draft, 'token'))->handle();

        Event::assertDispatched(DraftSyncStopped::class);
        Bus::assertNotDispatched(SyncDraftPicksJob::class);
        $this->assertTrue($this->draft->fresh()->is_completed);
        $this->assertFalse($this->draft->fresh()->is_active);
    }

    public function test_starting_and_stopping_the_sync(): void
    {
        Bus::fake([SyncDraftPicksJob::class]);

        $this->actingAs($this->user)
            ->post(route('drafts.sync.store', $this->draft))
            ->assertRedirect();

        $this->assertTrue($this->draft->fresh()->is_active);
        Bus::assertDispatched(SyncDraftPicksJob::class);

        $this->actingAs($this->user)
            ->delete(route('drafts.sync.destroy', $this->draft))
            ->assertRedirect();

        $this->assertFalse($this->draft->fresh()->is_active);
        $this->assertNull(Cache::get(SyncDraftPicksJob::tokenKey($this->draft)));
    }

    public function test_someone_outside_the_league_cannot_start_the_sync(): void
    {
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->post(route('drafts.sync.store', $this->draft))
            ->assertForbidden();
    }
}
