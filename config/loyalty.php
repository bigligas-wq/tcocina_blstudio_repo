<?php

return [
    'welcome_email' => [
        'enabled' => env('LOYALTY_WELCOME_EMAIL_ENABLED', true),
        'queue' => env('LOYALTY_WELCOME_EMAIL_QUEUE', false),
    ],
];
