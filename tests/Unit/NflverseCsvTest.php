<?php

namespace Tests\Unit;

use App\Services\Nflverse\Helpers\Csv;
use PHPUnit\Framework\TestCase;

/**
 * The reader runs over files big enough that a subtle parsing mistake is
 * invisible in the totals, so the awkward cases are pinned here.
 */
class NflverseCsvTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = tempnam(sys_get_temp_dir(), 'csv');
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    private function write(string $contents): string
    {
        file_put_contents($this->path, $contents);

        return $this->path;
    }

    public function test_it_keys_each_row_by_column_name(): void
    {
        $rows = iterator_to_array((new Csv)->rows($this->write(
            "player_id,receiving_yards\n00-0036322,1048\n"
        )));

        $this->assertCount(1, $rows);
        $this->assertSame(['player_id' => '00-0036322', 'receiving_yards' => '1048'], $rows[0]);
    }

    public function test_it_strips_a_byte_order_mark_from_the_first_column(): void
    {
        $rows = iterator_to_array((new Csv)->rows($this->write(
            "\u{FEFF}player_id,receiving_yards\n00-0036322,1048\n"
        )));

        $this->assertArrayHasKey('player_id', $rows[0]);
    }

    public function test_it_keeps_quoted_commas_inside_one_value(): void
    {
        $rows = iterator_to_array((new Csv)->rows($this->write(
            "player,college\n\"Smith, Jr., Steve\",\"Utah\"\n"
        )));

        $this->assertSame('Smith, Jr., Steve', $rows[0]['player']);
    }

    public function test_it_skips_a_row_that_does_not_match_the_header(): void
    {
        $rows = iterator_to_array((new Csv)->rows($this->write(
            "player_id,receiving_yards\n00-0036322\n00-0036322,1048\n"
        )));

        $this->assertCount(1, $rows);
    }

    public function test_it_yields_nothing_for_a_missing_file(): void
    {
        $this->assertSame([], iterator_to_array((new Csv)->rows('/no/such/file.csv')));
    }
}
