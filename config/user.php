<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default User Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the default values for creating new users via
    | the artisan command. These values can be overridden by setting
    | environment variables.
    |
    */

    'default' => [
        'email'    => env('DEFAULT_USER_EMAIL', 'user@test.com'),
        'password' => env('DEFAULT_USER_PASSWORD', 'password'),
        'name'     => env('DEFAULT_USER_NAME', 'Test User'),
    ],
];
