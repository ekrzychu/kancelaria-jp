<?php

return [
    'driver' => 'database',
    'cookie' => 'kjp_panel_session',
    'domain' => null,
    'path' => '/',
    'http_only' => true,
    'encrypt' => true,
    'same_site' => 'lax',
    'secure' => ! in_array(env('APP_ENV'), ['local', 'testing'], true)
        || str_starts_with((string) env('ADMIN_URL'), 'https://'),
    'lifetime' => 120,
    'table' => 'sessions',
];
