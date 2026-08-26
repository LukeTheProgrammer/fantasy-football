<?php

namespace App\Traits;

/**
 * Prints the rows an import could not store.
 *
 * A stale roster or a renamed player produces hundreds of them, so the default
 * is a count per reason and the full list is asked for.
 */
trait ReportsImportErrors
{
    /**
     * @param array{errors: array} $result
     */
    private function reportErrors(object $importer, array $result): void
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
}
