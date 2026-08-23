<?php

namespace App\Console\Commands\FantasyPros;

use App\Enums\FantasyProsDraftSlate;
use App\Facades\Import;
use App\Models\Season;
use Illuminate\Console\Command;

class ImportRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy-pros:rankings:import
        { --slate=*   : Limit to one or more draft boards, defaults to all }
        { --season=   : Season to import, defaults to the current season }
        { --captured= : Import a specific capture date instead of the newest }
        { --errors    : List every unresolved row rather than a count }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import archived FantasyPros draft boards into draft rankings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slates = $this->slates();

        if ($slates === false) {
            return self::FAILURE;
        }

        $season = (int) ($this->option('season') ?? Season::current()->first()?->id);

        $this->info('Importing ' . $season . ' draft rankings');

        $importer = Import::fantasyProsRankings();

        $result = $importer->import($season, $slates, $this->option('captured'));

        $this->table(
            ['Created', 'Updated', 'Skipped'],
            [[$result['created'], $result['updated'], $result['skipped']]]
        );

        $this->reportErrors($importer, $result);

        return self::SUCCESS;
    }

    /**
     * Unresolved rows are grouped by reason, since a stale roster produces
     * hundreds of them and the reason is what matters.
     */
    private function reportErrors($importer, array $result): void
    {
        if (empty($result['errors'])) {
            return;
        }

        if ($this->option('errors')) {
            $this->table(
                ['Reason', 'Data'],
                array_map(fn ($error) => [$error['reason'], json_encode($error['data'])], $result['errors'])
            );

            return;
        }

        $this->warn('Skipped rows:');

        $this->table(
            ['Reason', 'Rows'],
            $importer->errorSummary()->map(fn ($errors, $reason) => [$reason, $errors->count()])->values()->all()
        );

        $this->line('Run again with --errors to list them.');
    }

    /**
     * The boards to import, all of them unless the command names some.
     *
     * @return array<int, FantasyProsDraftSlate>|null|false Null imports every board.
     */
    private function slates()
    {
        $requested = $this->option('slate');

        if (empty($requested)) {
            return null;
        }

        $slates = [];

        foreach ($requested as $value) {
            $slate = FantasyProsDraftSlate::tryFrom($value);

            if (!$slate instanceof FantasyProsDraftSlate) {
                $this->error('Unknown board: ' . $value);
                $this->line('Available: ' . implode(', ', array_keys(FantasyProsDraftSlate::options())));

                return false;
            }

            $slates[] = $slate;
        }

        return $slates;
    }
}
