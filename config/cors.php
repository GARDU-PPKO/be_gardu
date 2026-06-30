<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('APP_URL', 'http://localhost'), 'http://localhost:5173', 'http://localhost:5174', 'http://127.0.0.1:5173', 'https://fe-gardu.vercel.app'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
