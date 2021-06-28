<?php

return [

    /*
    |--------------------------------------------------------------------------
    | A list of the exception types that should be captured.
    |--------------------------------------------------------------------------
    |
    | For which exception class emails should be sent?
    |
    | You can also use '*' in the array which will in turn captures every
    | exception.
    |
    */
    'capture' => [
        '*'
    ],

    /*
    |--------------------------------------------------------------------------
    | Error email recipients
    |--------------------------------------------------------------------------
    |
    | Email stack traces to these addresses.
    |
    */

    'to' => [
        // 'hello@example.com',
        'joosep.alviste@gmail.com',
        'ago.luberg@ttu.ee',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Crawler Bots
    |--------------------------------------------------------------------------
    |
    | For which bots should we NOT send error emails?
    |
    */
    'ignored_bots' => [
        'googlebot',        // Googlebot
        'bingbot',          // Microsoft Bingbot
        'slurp',            // Yahoo! Slurp
        'ia_archiver',      // Alexa
    ],
];
