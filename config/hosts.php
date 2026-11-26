<?php

return [
    'public' => env('PUBLIC_HOST', '127.0.0.1'),
    'public_url' => env('APP_URL', 'http://127.0.0.1:8743'),
    'admin' => env('ADMIN_HOST'),
    'admin_url' => env('ADMIN_URL'),
    'test_recovery' => (bool) env('ADMIN_TEST_RECOVERY', false),
];
