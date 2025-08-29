<?php

namespace App\Console\Commands;

use App\Models\DraftRanking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalculateAverageDraftRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'draft:calculate-rankings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills the average_rank column in the draft_rankings table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = DraftRanking::query()->orderBy('id');

        $bar = $this->output->createProgressBar($query->count());

        $query->lazy()->each(function ($draftRanking) use ($bar) {
            $draftRanking->update([
                'average_rank' => $this->getAvgRank($draftRanking),
                'average_value' => $this->getADV($draftRanking),
            ]);

            $bar->advance();
        });

        $bar->finish();
        echo PHP_EOL;
        $this->info('Average draft rankings calculated successfully!');
    }

    private function getAvgRank(DraftRanking $dr)
    {
        $rankings = array_filter([
            floatval($dr->fp_ranking),
        ]);

        $n = floatval(array_sum($rankings));
        $d = intval(count($rankings));

        return $d > 0 ? $n / $d : 0;
    }

    private function getADV(DraftRanking $dr)
    {
        $adv = array_filter([
            floatval($dr->fp_adv),
        ]);

        $n = floatval(array_sum($adv));
        $d = intval(count($adv));

        return $d > 0 ? $n / $d : 0;
    }
}
