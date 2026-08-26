<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use App\Services\Espn\Enums\FantasyNFLViews;
use Illuminate\Console\Command;

class GetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:get:data
        { league_id? : ESPN League ID }
        { --s2=      : ESPN S2 token }
        { --swid=    : ESPN SWID token }
        { --raw      : Save raw data }
        { --custom   : Save custom data }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League data from the ESPN API.';

    protected int|string $leagueId;

    protected string $outputPath;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setOutputPath();

        $this->leagueId = $this->argument('league_id') ?? config('services.espn.default_league_id');

        $fantasyNFL = Espn::fantasyNFL([
            'leagueId' => $this->leagueId,
            's2'       => $this->option('s2') ?? config('services.espn.default_s2'),
            'swid'     => $this->option('swid') ?? config('services.espn.default_swid'),
        ]);

        if ($this->option('raw')) {
            $fantasyNFL->returnRaw = true;
        }

        // rosterForTeamId=1
        // &view=mDraftDetail
        // &view=mLiveScoring
        // &view=mMatchupScore
        // &view=mPendingTransactions
        // &view=mPositionalRatings
        // &view=mRoster
        // &view=mSettings
        // &view=mTeam
        // &view=modular
        // &view=mNav

        $data = $fantasyNFL->getData([
            FantasyNFLViews::DRAFT,
            FantasyNFLViews::KONA,
            FantasyNFLViews::LIVE_SCORE,
            FantasyNFLViews::MATCHUP,
            FantasyNFLViews::MATCHUP_SCORE,
            FantasyNFLViews::MODULAR,
            FantasyNFLViews::NAV,
            FantasyNFLViews::PENDING_TRANSACTIONS,
            FantasyNFLViews::PLAYERS_WL,
            FantasyNFLViews::PLAYER_WL,
            FantasyNFLViews::POSITIONAL_RATINGS,
            FantasyNFLViews::PRO_TEAM_SCHEDULES_WL,
            FantasyNFLViews::ROSTER,
            FantasyNFLViews::SETTINGS,
            FantasyNFLViews::STANDINGS,
            FantasyNFLViews::STATUS,
            FantasyNFLViews::TEAM,
        ]);

        $this->saveData('keys', array_keys($data));

        $league = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->saveData($key, $value);
            } else {
                $league[$key] = $value;
            }
        }

        $this->saveData('league', $league);
    }

    protected function setOutputPath()
    {
        $parts = ['data', 'espn', 'ffl'];

        if ($this->option('raw')) {
            $parts[] = 'raw';
        }

        if ($this->option('custom')) {
            $parts[] = 'custom-' . date('Y-m-d-H-i');
        }

        $this->outputPath = storage_path(implode(DIRECTORY_SEPARATOR, $parts));
    }

    protected function filePath(string $key): string
    {
        $parts = [
            $this->outputPath,
            $this->leagueId . '-getData-' . $key . '.json',
        ];

        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    protected function saveData(string $key, array $value)
    {
        $path = $this->filePath($key);
        $dirPath = dirname($path);

        if ($this->option('custom') && !is_dir($dirPath)) {
            Log::debug('Creating Dir', [__CLASS__, $dirPath]);
            mkdir($dirPath, 0775, true);
        }

        $bytes = file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT));

        $this->info("NFL Fantasy League $key saved to $path ($bytes bytes)");
    }
}
