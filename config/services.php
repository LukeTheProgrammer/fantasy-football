<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'espn' => [
        'default_s2'        => env('ESPN_DEFAULT_S2'),
        'default_swid'      => env('ESPN_DEFAULT_SWID'),
        'default_league_id' => env('ESPN_DEFAULT_LEAGUE_ID'),
        'retry_limit'       => env('ESPN_RETRY_LIMIT', 2),
    ],

    'cbs' => [
        // Session cookies, used only for the HTML pages.
        'pid'   => env('CBS_PID'),
        'token' => env('CBS_TOKEN'),

        // The fantasy JSON API authenticates on CBSi.token instead, which is
        // scraped from any league page and expires within the day.
        'default_league_id'    => env('CBS_DEFAULT_LEAGUE_ID'),
        'default_access_token' => env('CBS_ACCESS_TOKEN'),
        'retry_limit'          => env('CBS_RETRY_LIMIT', 2),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'fantasy_pros' => [
        'key'      => env('FP_KEY'),
        'base_url' => env('FP_BASE_URL', 'https://api.fantasypros.com/public/v2/json'),
        'mcp_url'  => env('FP_MCP_URL', 'https://api.fantasypros.com/mcp'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
