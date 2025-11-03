<?php


return [
    'soap' => [
        'host' => env('TRINITY_SOAP_HOST', 'http://127.0.0.1:7878/'),
        'user' => env('TRINITY_SOAP_USER'),
        'pass' => env('TRINITY_SOAP_PASS'),
        'timeout' => (int)env('TRINITY_SOAP_TIMEOUT', 8),
        'verify' => filter_var(env('TRINITY_SOAP_VERIFY', true), FILTER_VALIDATE_BOOL),
    ],

    // optional: safe, whitelisted teleport locations for your UI later
    'teleport_presets' => [
         'Stormwind' => ['map' => 0, 'x' => -8913.23, 'y' => 554.63, 'z' => 93.79, 'o' => 0],
    ],
];
