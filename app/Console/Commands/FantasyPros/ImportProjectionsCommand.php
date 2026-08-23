<?php

namespace App\Console\Commands\FantasyPros;

use App\Enums\FantasyProsSlate;
use App\Facades\Import;
use App\Models\Season;
use App\Models\Week;
use Illuminate\Console\Command;

class ImportProjectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy-pros:projections:import
        { --slate=*   : Limit to one or more slates, defaults to all }
        { --season=   : Season to import, defaults to the current season }
        { --week=     : Week to import, defaults to the current week }
        { --captured= : Import a specific capture date instead of the newest }
        { --errors    : List every unresolved row rather than a count }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import archived FantasyPros slates into player projections';

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
        $week = (int) ($this->option('week') ?? Week::current()->first()?->week);

        $this->info('Importing ' . $season . ' week ' . $week . ' projections');

        $importer = Import::fantasyProsProjections();

        $result = $importer->import($season, $week, $slates, $this->option('captured'));

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
     * The slates to import, all of them unless the command names some.
     *
     * @return array<int, FantasyProsSlate>|null|false Null imports every slate.
     */
    private function slates()
    {
        $requested = $this->option('slate');

        if (empty($requested)) {
            return null;
        }

        $slates = [];

        foreach ($requested as $value) {
            $slate = FantasyProsSlate::tryFrom($value);

            if (!$slate instanceof FantasyProsSlate) {
                $this->error('Unknown slate: ' . $value);
                $this->line('Available: ' . implode(', ', array_keys(FantasyProsSlate::options())));

                return false;
            }

            $slates[] = $slate;
        }

        return $slates;
    }
}
