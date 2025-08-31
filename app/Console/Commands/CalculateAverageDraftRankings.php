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
        $vals = [
            $dr->fp_standard_ranking,
            $dr->fp_ppr_ranking,
            $dr->fp_dynasty_ranking,
        ];

        return $this->avg($vals);
    }

    private function getADV(DraftRanking $dr)
    {
        $vals = [
            $dr->fp_standard_adv,
            $dr->fp_ppr_adv,
            $dr->fp_dynasty_adv,
        ];

        return $this->avg($vals);
    }

    private function avg(array $vals)
    {
        $values = array_filter(
            array_map(function($val) {
                $float = floatval($val);
                return $float > 0 ? $float : null;
            }, $vals)
        );

        $n = array_sum($vals);
        $d = count($vals);

        return $d > 0 ? $n / $d : 0;
    }
}
