<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Boost Telescope Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable Laravel Boost Telescope entirely.
    |
    */
    'enabled' => env('LARAVEL_BOOST_TELESCOPE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Laravel Boost Telescope Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for Laravel Boost Telescope.
    |
    */
    'logging' => [
        'enabled' => env('LARAVEL_BOOST_TELESCOPE_LOGGING_ENABLED', true),
        'channel' => env('LARAVEL_BOOST_TELESCOPE_LOG_CHANNEL', 'stack'),
    ],
];
