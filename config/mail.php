<?php

return [
    'default' => in_array(env('APP_ENV'), ['local', 'testing'], true) ? 'array' : env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'array' => ['transport' => 'array'],
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME', 'smtps'),
            'host' => env('MAIL_HOST'),
            'port' => (int) env('MAIL_PORT', 465),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'require_tls' => true,
            'timeout' => 15,
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'contact@example.test'),
        'name' => env('MAIL_FROM_NAME', 'Formularz kontaktowy kancelarii'),
    ],

    'contact' => [
        'to' => env('CONTACT_MAIL_TO', 'kontakt@kancelaria-jp.pl'),
        'retention_days' => (int) env('CONTACT_RETENTION_DAYS', 0),
    ],
];
