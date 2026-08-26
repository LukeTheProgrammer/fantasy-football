<?php

namespace Tests\Unit;

use App\Services\Espn\Helpers\FantasyPlayerId;
use App\Services\Player\Helpers\NormalizedName;
use PHPUnit\Framework\TestCase;

/**
 * The differences between how two sources spell the same man.
 */
class NormalizedNameTest extends TestCase
{
    public function test_generational_suffixes_come_off(): void
    {
        $this->assertSame(NormalizedName::of('Erick All'), NormalizedName::of('Erick All Jr.'));
        $this->assertSame(NormalizedName::of('Phillip Dorsett'), NormalizedName::of('Phillip Dorsett II'));
        $this->assertSame(NormalizedName::of('Pierre Strong'), NormalizedName::of('Pierre Strong Jr'));
    }

    public function test_punctuation_does_not_decide_identity(): void
    {
        $this->assertSame(NormalizedName::of('AJ Henning'), NormalizedName::of('A.J. Henning'));
        $this->assertSame(NormalizedName::of('Jamarr Chase'), NormalizedName::of("Ja'Marr Chase"));
        $this->assertSame(NormalizedName::of('Amon Ra St Brown'), NormalizedName::of('Amon-Ra St. Brown'));
    }

    public function test_accents_fold_to_their_base_letters(): void
    {
        $this->assertSame(NormalizedName::of('Amon-Ra'), NormalizedName::of('Amón-Ra'));
    }

    public function test_roster_notes_and_award_markers_are_not_part_of_a_name(): void
    {
        $this->assertSame('justin jefferson', NormalizedName::of('Justin Jefferson (IR)'));
        $this->assertSame('justin jefferson', NormalizedName::of('Justin Jefferson*+'));
    }

    public function test_it_reduces_rather_than_guesses(): void
    {
        // Two different men, and nothing here should bring them together.
        $this->assertNotSame(NormalizedName::of('Josh Johnson'), NormalizedName::of('Josh Johnston'));
        $this->assertNotSame(NormalizedName::of('Mike Williams'), NormalizedName::of('Michael Williams'));
    }

    public function test_an_empty_name_normalises_to_nothing(): void
    {
        $this->assertNull(NormalizedName::of(null));
        $this->assertNull(NormalizedName::of('   '));
    }

    public function test_espn_gives_team_defenses_a_negative_id_built_from_the_team(): void
    {
        $ids = new FantasyPlayerId;

        // Green Bay is ESPN team 9, so their defense is player -16009.
        $this->assertTrue($ids->isDefense(-16009));
        $this->assertSame(9, $ids->espnId(-16009));
        $this->assertSame('DST', $ids->lookup(-16009)['position_id']);

        $this->assertFalse($ids->isDefense(3929630));
        $this->assertSame(3929630, $ids->espnId(3929630));
        $this->assertArrayNotHasKey('position_id', $ids->lookup(3929630));
    }
}
