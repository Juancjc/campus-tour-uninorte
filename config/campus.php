<?php

return [
    'force_https' => (bool) env('FORCE_HTTPS', false),
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrador'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
    'mail_api' => [
        'url' => env('MAIL_API_URL', 'https://nest.juancjc.com.br/api-nest-central-jc'),
        'key' => env('MAIL_API_KEY'),
        'token' => env('MAIL_API_TOKEN'),
        'connect_timeout' => (int) env('MAIL_API_CONNECT_TIMEOUT', 3),
        'timeout' => (int) env('MAIL_API_TIMEOUT', 10),
    ],
];
