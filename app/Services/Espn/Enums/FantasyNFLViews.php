<?php

namespace App\Services\Espn\Enums;

enum FantasyNFLViews: string
{
    case DRAFT                 = 'mDraftDetail';
    case KONA                  = 'kona_player_info';
    case LIVE_SCORE            = 'mLiveScoring';
    case MATCHUP               = 'mMatchup';
    case MATCHUP_SCORE         = 'mMatchupScore';
    case MODULAR               = 'modular';
    case NAV                   = 'mNav';
    case PENDING_TRANSACTIONS  = 'mPendingTransactions';
    case PLAYERS_WL            = 'players_wl';
    case PLAYER_WL             = 'player_wl';
    case POSITIONAL_RATINGS    = 'mPositionalRatings';
    case PRO_TEAM_SCHEDULES_WL = 'proTeamSchedules_wl';
    case ROSTER                = 'mRoster';
    case SETTINGS              = 'mSettings';
    case STANDINGS             = 'mStandings';
    case STATUS                = 'mStatus';
    case TEAM                  = 'mTeam';
}
