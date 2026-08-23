<?php

namespace Tests\Feature;

use App\Services\Data\DataArchive;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DataArchiveTest extends TestCase
{
    private string $source = 'test-source';

    protected function tearDown(): void
    {
        $this->deleteDirectory(storage_path('data/' . $this->source));

        parent::tearDown();
    }

    private function archive(int $season = 2026, int $week = 1): DataArchive
    {
        return DataArchive::for($this->source, 'rankings', $season, $week);
    }

    public function test_it_files_a_capture_under_the_date_it_was_pulled(): void
    {
        Carbon::setTestNow('2026-08-23 10:00:00');

        $path = $this->archive()->put('ppr-wr', '<html></html>', 'html');

        $this->assertStringEndsWith(
            'data/' . $this->source . '/rankings/2026/week-1/2026-08-23/ppr-wr.html',
            $path
        );
        $this->assertFileExists($path);
    }

    public function test_it_reads_back_the_newest_capture(): void
    {
        $archive = $this->archive();

        $archive->putJson('ppr-wr', ['day' => 'older'], '2026-08-20');
        $archive->putJson('ppr-wr', ['day' => 'newer'], '2026-08-22');

        $this->assertSame(['day' => 'newer'], $archive->getJson('ppr-wr'));
    }

    public function test_it_reads_a_specific_capture_date(): void
    {
        $archive = $this->archive();

        $archive->putJson('ppr-wr', ['day' => 'older'], '2026-08-20');
        $archive->putJson('ppr-wr', ['day' => 'newer'], '2026-08-22');

        $this->assertSame(['day' => 'older'], $archive->getJson('ppr-wr', '2026-08-20'));
        $this->assertNull($archive->getJson('ppr-wr', '2026-08-21'));
    }

    public function test_a_later_capture_does_not_overwrite_an_earlier_one(): void
    {
        $archive = $this->archive();

        $archive->putJson('ppr-wr', ['day' => 'older'], '2026-08-20');
        $archive->putJson('ppr-wr', ['day' => 'newer'], '2026-08-22');

        $this->assertSame(['day' => 'older'], $archive->getJson('ppr-wr', '2026-08-20'));
        $this->assertEquals(['2026-08-22', '2026-08-20'], $archive->captures()->all());
    }

    public function test_it_falls_back_to_a_file_from_the_previous_flat_layout(): void
    {
        $archive = $this->archive();

        $weekDirectory = storage_path('data/' . $this->source . '/rankings/2026/week-1');
        mkdir($weekDirectory, 0775, true);
        file_put_contents($weekDirectory . '/ppr-wr.json', json_encode(['layout' => 'legacy']));

        $this->assertTrue($archive->has('ppr-wr'));
        $this->assertSame(['layout' => 'legacy'], $archive->getJson('ppr-wr'));
    }

    public function test_a_dated_capture_wins_over_the_legacy_flat_file(): void
    {
        $archive = $this->archive();

        $weekDirectory = storage_path('data/' . $this->source . '/rankings/2026/week-1');
        mkdir($weekDirectory, 0775, true);
        file_put_contents($weekDirectory . '/ppr-wr.json', json_encode(['layout' => 'legacy']));

        $archive->putJson('ppr-wr', ['layout' => 'dated'], '2026-08-22');

        $this->assertSame(['layout' => 'dated'], $archive->getJson('ppr-wr'));
    }

    public function test_it_knows_whether_a_file_was_captured_today(): void
    {
        Carbon::setTestNow('2026-08-23 10:00:00');

        $archive = $this->archive();

        $this->assertFalse($archive->capturedToday('ppr-wr'));

        $archive->putJson('ppr-wr', ['captured' => true]);

        $this->assertTrue($archive->capturedToday('ppr-wr'));
    }

    public function test_missing_data_reads_as_null_rather_than_failing(): void
    {
        $archive = $this->archive(2099, 18);

        $this->assertFalse($archive->has('nothing'));
        $this->assertNull($archive->get('nothing'));
        $this->assertNull($archive->getJson('nothing'));
        $this->assertTrue($archive->captures()->isEmpty());
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (array_diff(scandir($path), ['.', '..']) as $entry) {
            $entryPath = $path . '/' . $entry;

            is_dir($entryPath) ? $this->deleteDirectory($entryPath) : unlink($entryPath);
        }

        rmdir($path);
    }
}
