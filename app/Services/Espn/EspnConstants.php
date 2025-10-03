<?php

namespace App\Services\Espn;

use App\Enums\NFLPositions;
use App\Enums\NFLTeams;
use Illuminate\Support\Arr;

class EspnConstants
{
    public const TEAM_ID_MAP = [
        NFLTeams::ARI => '22',
        NFLTeams::ATL => '1',
        NFLTeams::BAL => '33',
        NFLTeams::BUF => '2',
        NFLTeams::CAR => '29',
        NFLTeams::CHI => '3',
        NFLTeams::CIN => '4',
        NFLTeams::CLE => '5',
        NFLTeams::DAL => '6',
        NFLTeams::DEN => '7',
        NFLTeams::DET => '8',
        NFLTeams::GB  => '9',
        NFLTeams::HOU => '34',
        NFLTeams::IND => '11',
        NFLTeams::JAX => '30',
        NFLTeams::KC  => '12',
        NFLTeams::LAC => '24',
        NFLTeams::LAR => '14',
        NFLTeams::LV  => '13',
        NFLTeams::MIA => '15',
        NFLTeams::MIN => '16',
        NFLTeams::NE  => '17',
        NFLTeams::NO  => '18',
        NFLTeams::NYG => '19',
        NFLTeams::NYJ => '20',
        NFLTeams::PHI => '21',
        NFLTeams::PIT => '23',
        NFLTeams::SEA => '26',
        NFLTeams::SF  => '25',
        NFLTeams::TB  => '27',
        NFLTeams::TEN => '10',
        NFLTeams::WSH => '28',
    ];

    public const POSITION_MAP = [
        'QB'  => NFLPositions::QB,
        'RB'  => NFLPositions::RB,
        'WR'  => NFLPositions::WR,
        'TE'  => NFLPositions::TE,
        'DT'  => NFLPositions::DT,
        'DE'  => NFLPositions::DE,
        'LB'  => NFLPositions::LB,
        'CB'  => NFLPositions::CB,
        'S'   => NFLPositions::S,
        'DB'  => NFLPositions::CB,
        'DST' => NFLPositions::DST,
        'K'   => NFLPositions::K,
        'P'   => NFLPositions::P,
        'PK'  => NFLPositions::K,
    ];

    // ESPN => LeagueSettings Model
    public const SCORING_MAP = [
        // Passing
        'passingYards'            => 'passing_points_per_yard',
        'passingTouchdowns'       => 'passing_td_points',
        'passingInterceptions'    => 'interception_points',

        // Rushing
        'rushingYards'            => 'rushing_points_per_yard',
        'rushingTouchdowns'       => 'rushing_td_points',

        // Receiving
        'receivingYards'          => 'receiving_points_per_yard',
        'receivingTouchdowns'     => 'receiving_td_points',
        'receivingReceptions'     => 'reception_points',

        // Misc
        'lostFumbles'             => 'fumble_lost_points',
        '2PtConversions'          => 'two_point_conversion_points',

        // Kicking
        'fieldGoal0To39'          => 'field_goal_0_39_points',
        'fieldGoal40To49'         => 'field_goal_40_49_points',
        'fieldGoal50Plus'         => 'field_goal_50_plus_points',
        'extraPoint'              => 'extra_point_points',

        // DST
        'defenseSacks'            => 'defense_sack_points',
        'defenseInterceptions'    => 'defense_interception_points',
        'defenseFumbleRecoveries' => 'defense_fumble_recovery_points',
        'defenseTouchdowns'       => 'defense_td_points',
        'defenseSafeties'         => 'defense_safety_points',
    ];

    public const POSITION_SLOT_MAP = [
        0  => 'QB',       // Quarterback (QB)
        1  => 'TQB',      // Team Quarterback (TQB)
        2  => 'RB',       // Running Back (RB)
        3  => 'RB_WR',    // Running Back/Wide Receiver (RB/WR)
        4  => 'WR',       // Wide Receiver (WR)
        5  => 'WR_TE',    // Wide Receiver/Tight End (WR/TE)
        6  => 'TE',       // Tight End (TE)
        7  => 'OP',       // Offensive Player Utility (OP)
        8  => 'DT',       // Defensive Tackle (DT)
        9  => 'DE',       // Defensive End (DE)
        10 => 'LB',       // Linebacker (LB)
        11 => 'DL',       // Defensive Line (DL)
        12 => 'CB',       // Cornerback (CB)
        13 => 'S',        // Safety (S)
        14 => 'DB',       // Defensive Back (DB)
        15 => 'DP',       // Defensive Player Utility (DP)
        16 => 'DST',      // Team Defense/Special Teams (D/ST)
        17 => 'K',        // Place Kicker (K)
        18 => 'P',        // Punter (P)
        19 => 'HC',       // Head Coach (HC)
        20 => 'BE',       // Bench (BE)
        21 => 'IR',       // Injured Reserve (IR)
        23 => 'RB_WR_TE', // Flex (FLEX)
        25 => 'Rookie',
    ];

    public const POSITION_LIMIT_MAP = [
        1  => 'QB',
        2  => 'RB',
        3  => 'WR',
        4  => 'TE',
        5  => 'K',
        7  => 'P',
        9  => 'DT',
        10 => 'DE',
        11 => 'LB',
        12 => 'CB',
        13 => 'S',
        14 => 'HC',
        16 => 'DST',
    ];

    public const PLAYER_STATS_MAP = [
        // Passing Stats
        0 => 'passingAttempts', // PA
        1 => 'passingCompletions', // PC
        2 => 'passingIncompletions', // INC
        3 => 'passingYards', // PY
        4 => 'passingTouchdowns', // PTD
        // 5-14 appear for passing players
        // 5-7 => 6 is half of 5 (integer divide by 2), 7 is half of 6 (integer divide by 2)
        // 8-10 => 9 is half of 8 (integer divide by 2), 10 is half of 9 (integer divide by 2)
        // 11-12 => 12 is half of 11 (integer divide by 2)
        // 13-14 => 14 is half of 13 (integer divide by 2)
        15 => 'passing40PlusYardTD', // PTD40
        16 => 'passing50PlusYardTD', // PTD50
        17 => 'passing300To399YardGame', // P300
        18 => 'passing400PlusYardGame', // P400
        19 => 'passing2PtConversions', // 2PC
        20 => 'passingInterceptions', // INT
        21 => 'passingCompletionPercentage',
        22 => 'passingYards', // PY - TODO: figure out what the difference is between 22 and 3

        // Rushing Stats
        23 => 'rushingAttempts', // RA
        24 => 'rushingYards', // RY
        25 => 'rushingTouchdowns', // RTD
        26 => 'rushing2PtConversions', // 2PR
        // 27-34 appear for rushing players
        // 27-29 => 28 is half of 27 (integer divide by 2), 29 is half of 28 (integer divide by 2)
        // 30-32 => 31 is half of 30 (integer divide by 2), 32 is half of 31 (integer divide by 2)
        // 33-34 => 34 is half of 33 (integer divide by 2)
        35 => 'rushing40PlusYardTD', // RTD40
        36 => 'rushing50PlusYardTD', // RTD50
        37 => 'rushing100To199YardGame', // RY100
        38 => 'rushing200PlusYardGame', // RY200
        39 => 'rushingYardsPerAttempt',
        40 => 'rushingYards', // RY - TODO: figure out what the difference is between 40 and 24

        // Receiving Stats
        41 => 'receivingReceptions', // REC
        42 => 'receivingYards', // REY
        43 => 'receivingTouchdowns', // RETD
        44 => 'receiving2PtConversions', // 2PRE
        45 => 'receiving40PlusYardTD', // RETD40
        46 => 'receiving50PlusYardTD', // RETD50
        // 47-52 appear for receiving players
        // 47-49 => 48 is half of 47 (integer divide by 2), 49 is half of 48 (integer divide by 2)
        // 50-52 => 51 is half of 50 (integer divide by 2), 52 is half of 51 (integer divide by 2)
        53 => 'receivingReceptions', // REC - TODO: figure out what the difference is between 53 and 41
        // 54-55 appear for receiving players
        // 54-55 => 55 is half of 54 (integer divide by 2)
        56 => 'receiving100To199YardGame', // REY100
        57 => 'receiving200PlusYardGame', // REY200
        58 => 'receivingTargets', // RET
        59 => 'receivingYardsAfterCatch',
        60 => 'receivingYardsPerReception',
        61 => 'receivingYards', // REY - TODO: figure out what the difference is between 61 and 42
        62 => '2PtConversions',
        63 => 'fumbleRecoveredForTD', // FTD
        64 => 'passingTimesSacked', // SK

        68 => 'fumbles', // FUM

        72 => 'lostFumbles', // FUML
        73 => 'turnovers',

        // Kicking Stats
        74 => 'madeFieldGoalsFrom50Plus', // FG50 (does not map directly to FG50 as FG50 does not include 60+)
        75 => 'attemptedFieldGoalsFrom50Plus', // FGA50 (does not map directly to FGA50 as FG50 does not include 60+)
        76 => 'missedFieldGoalsFrom50Plus', // FGM50 (does not map directly to FGM50 as FG50 does not include 60+)
        77 => 'madeFieldGoalsFrom40To49', // FG40
        78 => 'attemptedFieldGoalsFrom40To49', // FGA40
        79 => 'missedFieldGoalsFrom40To49', // FGM40
        80 => 'madeFieldGoalsFromUnder40', // FG0
        81 => 'attemptedFieldGoalsFromUnder40', // FGA0
        82 => 'missedFieldGoalsFromUnder40', // FGM0
        83 => 'madeFieldGoals', // FG
        84 => 'attemptedFieldGoals', // FGA
        85 => 'missedFieldGoals', // FGM
        86 => 'madeExtraPoints', // PAT
        87 => 'attemptedExtraPoints', // PATA
        88 => 'missedExtraPoints', // PATM

        // Defensive Stats
        89 => 'defensive0PointsAllowed', // PA0
        90 => 'defensive1To6PointsAllowed', // PA1
        91 => 'defensive7To13PointsAllowed', // PA7
        92 => 'defensive14To17PointsAllowed', // PA14
        93 => 'defensiveBlockedKickForTouchdowns', // BLKKRTD
        94 => 'defensiveTouchdowns', // Does not include defensive blocked kick for touchdowns (BLKKRTD)
        95 => 'defensiveInterceptions', // INT
        96 => 'defensiveFumbles', // FR
        97 => 'defensiveBlockedKicks', // BLKK
        98 => 'defensiveSafeties', // SF
        99 => 'defensiveSacks', // SK
        // 100 => This appears to be defensiveSacks * 2
        101 => 'kickoffReturnTouchdowns', // KRTD
        102 => 'puntReturnTouchdowns', // PRTD
        103 => 'interceptionReturnTouchdowns', // INTTD
        104 => 'fumbleReturnTouchdowns', // FRTD
        105 => 'defensivePlusSpecialTeamsTouchdowns', // Includes defensive blocked kick for touchdowns (BLKKRTD) and kickoff/punt return touchdowns
        106 => 'defensiveForcedFumbles', // FF
        107 => 'defensiveAssistedTackles', // TKA
        108 => 'defensiveSoloTackles', // TKS
        109 => 'defensiveTotalTackles', // TK

        113 => 'defensivePassesDefensed', // PD
        114 => 'kickoffReturnYards', // KR
        115 => 'puntReturnYards', // PR

        118 => 'puntsReturned', // PTR

        120 => 'defensivePointsAllowed', // PA
        121 => 'defensive18To21PointsAllowed', // PA18
        122 => 'defensive22To27PointsAllowed', // PA22
        123 => 'defensive28To34PointsAllowed', // PA28
        124 => 'defensive35To45PointsAllowed', // PA35
        125 => 'defensive45PlusPointsAllowed', // PA46

        127 => 'defensiveYardsAllowed', // YA
        128 => 'defensiveLessThan100YardsAllowed', #YA100
        129 => 'defensive100To199YardsAllowed', // YA199
        130 => 'defensive200To299YardsAllowed', // YA299
        131 => 'defensive300To349YardsAllowed', // YA349
        132 => 'defensive350To399YardsAllowed', // YA399
        133 => 'defensive400To449YardsAllowed', // YA449
        134 => 'defensive450To499YardsAllowed', // YA499
        135 => 'defensive500To549YardsAllowed', // YA549
        136 => 'defensive550PlusYardsAllowed', // YA550

        // Punter Stats
        138 => 'netPunts', // PT
        139 => 'puntYards', // PTY
        140 => 'puntsInsideThe10', // PT10
        141 => 'puntsInsideThe20', // PT20
        142 => 'blockedPunts', // PTB
        145 => 'puntTouchbacks', // PTTB
        146 => 'puntFairCatches', #PTFC
        147 => 'puntAverage',
        148 => 'puntAverage44.0+', // PTA44
        149 => 'puntAverage42.0-43.9', #PTA42
        150 => 'puntAverage40.0-41.9', #PTA40
        151 => 'puntAverage38.0-39.9', #PTA38
        152 => 'puntAverage36.0-37.9', #PTA36
        153 => 'puntAverage34.0-35.9', #PTA34
        154 => 'puntAverage33.9AndUnder', #PTA33

        // Head Coach Stats
        155 => 'teamWin', // TW
        156 => 'teamLoss', // TL
        157 => 'teamTie', // TIE
        158 => 'pointsScored', // PTS

        160 => 'pointsMargin',
        161 => '25+pointWinMargin', // WM25
        162 => '20-24pointWinMargin', // WM20
        163 => '15-19pointWinMargin', // WM15
        164 => '10-14pointWinMargin', // WM10
        165 => '5-9pointWinMargin', // WM5
        166 => '1-4pointWinMargin', // WM1
        167 => '1-4pointLossMargin', // LM1
        168 => '5-9pointLossMargin', // LM5
        169 => '10-14pointLossMargin', // LM10
        170 => '15-19pointLossMargin', // LM15
        171 => '20-24pointLossMargin', // LM20
        172 => '25+pointLossMargin', // LM25
        174 => 'winPercentage', // Value goes from 0-1

        187 => 'defensivePointsAllowed', // TODO: figure out what the difference is between 187 and 120

        201 => 'madeFieldGoalsFrom60Plus', // FG60
        202 => 'attemptedFieldGoalsFrom60Plus', // FGA60
        203 => 'missedFieldGoalsFrom60Plus', // FGM60

        205 => 'defensive2PtReturns', // 2PTRET
        206 => 'defensive2PtReturns', // 2PTRET - TODO: figure out what the difference is between 206 and 205
    ];

    public static function mapPlayerStats(array $stats): array
    {
        $mappedStats = [];

        foreach ($stats as $statId => $statValue) {
            $stat = Arr::get(static::PLAYER_STATS_MAP, $statId, false);

            if ($stat) {
                $mappedStats[$stat] = $statValue;
            }
        }

        return $mappedStats;
    }
}
