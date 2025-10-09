<?php

namespace App\Console\Commands\Data;

use App\Models\Week;
use Illuminate\Console\Command;

class SetCurrentWeekCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:set-current-week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the current week.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Week::where('is_current', true)->update(['is_current' => false]);

        Week::whereDate('ends_at', '>', date('Y-m-d'))
            ->orderBy('ends_at', 'asc')
            ->limit(1)
            ->update(['is_current' => true]);

        return Command::SUCCESS;
    }
}
