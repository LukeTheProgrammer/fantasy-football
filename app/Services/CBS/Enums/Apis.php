<?php

namespace App\Services\CBS\Enums;

enum Apis: string
{
    case FANTASY = 'fantasy.espn.com';
    case GAMBIT = 'gambit-api.fantasy.espn.com';
    case LM_READS = 'lm-api-reads.fantasy.espn.com';
    case NOW_CORE = 'now.core.api.espn.com';
    case PARTNERS = 'partners.api.espn.com';
    case SITE = 'site.api.espn.com';
    case SITE_WEB = 'site.web.api.espn.com';
    case SPORTS_CORE = 'sports.core.api.espn.com';
}
