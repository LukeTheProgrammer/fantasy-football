<?php

namespace Tests\Feature;

use App\Facades\Player as PlayerFacade;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\PlayerMissing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The finder decides who a row of data is about, which is the decision every
 * import depends on and the one that is most expensive to get wrong.
 */
class PlayerFinderTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_source_id_wins_over_everything_else(): void
    {
        $player = Player::factory()->create(['gsis_id' => '00-0036322', 'full_name' => 'Justin Jefferson']);
        Player::factory()->create(['full_name' => 'Justin Jefferson']);

        $found = PlayerFacade::find(['gsis_id' => '00-0036322', 'full_name' => 'Someone Else']);

        $this->assertSame($player->id, $found->id);
    }

    public function test_a_suffix_does_not_hide_a_player(): void
    {
        $player = Player::factory()->create(['full_name' => 'Erick All', 'position_id' => 'TE']);

        $found = PlayerFacade::find(['full_name' => 'Erick All Jr.', 'position_id' => 'TE']);

        $this->assertSame($player->id, $found->id);
    }

    public function test_punctuation_does_not_hide_a_player(): void
    {
        $player = Player::factory()->create(['full_name' => 'A.J. Henning', 'position_id' => 'WR']);

        $found = PlayerFacade::find(['full_name' => 'AJ Henning', 'position_id' => 'WR']);

        $this->assertSame($player->id, $found->id);
    }

    public function test_an_alias_is_matched_on_its_reduced_form_too(): void
    {
        $player = Player::factory()->create(['full_name' => 'Robert Griffin']);
        PlayerAlias::create(['player_ulid' => $player->ulid, 'name' => 'Robert Griffin III']);

        $found = PlayerFacade::find(['full_name' => 'Robert Griffin  III']);

        $this->assertSame($player->id, $found->id);
    }

    public function test_a_shared_name_is_left_unresolved_rather_than_guessed(): void
    {
        Player::factory()->create(['full_name' => 'Josh Johnson', 'position_id' => 'QB']);
        Player::factory()->create(['full_name' => 'Josh Johnson', 'position_id' => 'RB']);

        $found = PlayerFacade::find(['full_name' => 'Josh Johnson'], ['record_missing' => false]);

        $this->assertNull($found);
    }

    public function test_a_shared_name_resolves_once_position_and_team_narrow_it(): void
    {
        Player::factory()->create(['full_name' => 'Josh Johnson', 'position_id' => 'QB', 'team_id' => 'BAL']);
        $back = Player::factory()->create(['full_name' => 'Josh Johnson', 'position_id' => 'RB', 'team_id' => 'SEA']);

        $found = PlayerFacade::find([
            'full_name'   => 'Josh Johnson Jr.',
            'position_id' => 'RB',
            'team_id'     => 'SEA',
        ]);

        $this->assertSame($back->id, $found->id);
    }

    public function test_a_miss_carrying_only_an_id_is_still_recorded(): void
    {
        // A draft pick arrives as nothing but an ESPN id, and an unrecorded
        // miss there is a pick that silently vanishes from the draft.
        $found = PlayerFacade::find(['espn_id' => 999999]);

        $this->assertNull($found);
        $this->assertSame(1, PlayerMissing::count());
        $this->assertSame('999999', PlayerMissing::first()->unique_id_value);
    }
}
