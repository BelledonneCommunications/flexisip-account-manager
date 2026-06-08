<?php

return [
    'secret'            => env('HCAPTCHA_SECRET'),
    'sitekey'           => env('HCAPTCHA_SITEKEY'),
    'enabled'           => env('HCAPTCHA_ENABLED', true),
    'server-get-config' => false,
    'options'           => [
        'timeout' => 30,
    ],
];
