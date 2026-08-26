<?php

namespace Tests\Feature;

use App\Enums\Datum;
use App\Facades\Import;
use App\Models\DraftRanking;
use App\Models\Player;
use App\Services\Espn\Formatters\FantasyPlayerPoolFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspnRankingsImporterTest extends TestCase
{
    use RefreshDatabase;

    private function pool(array ...$players): array
    {
        return ['players' => array_map(fn (array $player) => ['player' => array_merge([
            'id'        => 4262921,
            'fullName'  => 'Justin Jefferson',
            'ownership' => [
                'auctionValueAverage'  => 54.152219,
                'averageDraftPosition' => 5.9651,
            ],
        ], $player)], $players ?: [[]])];
    }

    public function test_it_shapes_the_pool_into_a_board(): void
    {
        $rows = FantasyPlayerPoolFormatter::from($this->pool(), 2026);

        $this->assertCount(1, $rows);
        $this->assertSame(4262921, $rows[0]['espn_id']);
        $this->assertSame(54.15, $rows[0]['adv']);
        $this->assertSame(5.97, $rows[0]['adp']);
    }

    public function test_a_team_defense_is_recognised_despite_its_negative_id(): void
    {
        $rows = FantasyPlayerPoolFormatter::from($this->pool([
            'id'       => -16009,
            'fullName' => 'Packers D/ST',
        ]), 2026);

        $this->assertSame(9, $rows[0]['espn_id']);
        $this->assertSame('DST', $rows[0]['position_id']);
    }

    public function test_a_player_nobody_owns_or_pays_for_is_left_out(): void
    {
        $rows = FantasyPlayerPoolFormatter::from($this->pool([
            'ownership' => ['auctionValueAverage' => 0, 'averageDraftPosition' => 0],
        ]), 2026);

        $this->assertCount(0, $rows);
    }

    public function test_it_stores_the_board_under_its_own_source(): void
    {
        $player = Player::factory()->create(['espn_id' => 4262921]);

        $result = Import::espnRankings()->import(
            2026,
            FantasyPlayerPoolFormatter::from($this->pool(), 2026),
            '2026-08-26'
        );

        $this->assertSame(1, $result['created']);

        $ranking = DraftRanking::sole();

        $this->assertSame($player->id, $ranking->player_id);
        $this->assertSame(2026, $ranking->season);
        $this->assertSame(Datum::SOURCE_ESPN->value, $ranking->source);
        $this->assertSame('2026-08-26', $ranking->ranked_at->toDateString());
        $this->assertSame('54.15', $ranking->adv);
        $this->assertSame('5.97', $ranking->adp);
        $this->assertSame(1, $ranking->rank);
    }

    public function test_the_board_is_ordered_by_draft_position(): void
    {
        Player::factory()->create(['espn_id' => 1]);
        Player::factory()->create(['espn_id' => 2]);

        Import::espnRankings()->import(2026, FantasyPlayerPoolFormatter::from($this->pool(
            ['id' => 1, 'ownership' => ['auctionValueAverage' => 20, 'averageDraftPosition' => 40.5]],
            ['id' => 2, 'ownership' => ['auctionValueAverage' => 55, 'averageDraftPosition' => 2.1]],
        ), 2026), '2026-08-26');

        $ranks = DraftRanking::query()->orderBy('rank')->pluck('adp')->map(fn ($adp) => (float) $adp);

        $this->assertSame([2.1, 40.5], $ranks->all());
    }

    public function test_it_does_not_disturb_another_sources_board(): void
    {
        $player = Player::factory()->create(['espn_id' => 4262921]);

        $fantasyPros = DraftRanking::create([
            'player_id' => $player->id,
            'season'    => 2026,
            'ranked_at' => '2026-08-26',
            'type'      => 'redraft',
            'source'    => Datum::SOURCE_FANTASY_PROS->value,
            'ppr'       => 0,
            'superflex' => false,
            'rank'      => 12,
        ]);

        Import::espnRankings()->import(2026, FantasyPlayerPoolFormatter::from($this->pool(), 2026), '2026-08-26');

        // Same player, same day, same format columns — only the source differs,
        // and that is what keeps the two boards apart.
        $this->assertSame(2, DraftRanking::count());
        $this->assertSame(12, $fantasyPros->fresh()->rank);
        $this->assertNull($fantasyPros->fresh()->adv);
    }

    public function test_a_second_run_on_the_same_day_updates_rather_than_duplicates(): void
    {
        Player::factory()->create(['espn_id' => 4262921]);

        $rows = FantasyPlayerPoolFormatter::from($this->pool(), 2026);

        Import::espnRankings()->import(2026, $rows, '2026-08-26');
        $result = Import::espnRankings()->import(2026, $rows, '2026-08-26');

        $this->assertSame(1, DraftRanking::count());
        $this->assertSame(1, $result['updated']);
    }

    public function test_a_later_day_is_a_new_capture_rather_than_an_overwrite(): void
    {
        Player::factory()->create(['espn_id' => 4262921]);

        Import::espnRankings()->import(2026, FantasyPlayerPoolFormatter::from($this->pool(), 2026), '2026-08-25');
        Import::espnRankings()->import(2026, FantasyPlayerPoolFormatter::from($this->pool([
            'ownership' => ['auctionValueAverage' => 61.0, 'averageDraftPosition' => 4.1],
        ]), 2026), '2026-08-26');

        // A player's value moving through the summer is the point of keeping a
        // board per day.
        $this->assertSame(2, DraftRanking::count());
    }

    public function test_it_knows_whether_a_day_is_already_stored(): void
    {
        Player::factory()->create(['espn_id' => 4262921]);
        $importer = Import::espnRankings();

        $this->assertFalse($importer->capturedOn(2026, '2026-08-26'));

        $importer->import(2026, FantasyPlayerPoolFormatter::from($this->pool(), 2026), '2026-08-26');

        $this->assertTrue($importer->capturedOn(2026, '2026-08-26'));
        $this->assertFalse($importer->capturedOn(2026, '2026-08-27'));
    }

    public function test_an_unknown_player_is_reported_rather_than_created(): void
    {
        $result = Import::espnRankings()->import(2026, FantasyPlayerPoolFormatter::from($this->pool(), 2026));

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, Player::count());
    }
}
