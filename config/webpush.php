<?php

return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@banjarnegara.go.id'),
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
        'pem_file' => env('VAPID_PEM_FILE', null),
    ],
    'options' => [
        'TTL' => (int) env('WEB_PUSH_TTL', 86400), // 24 jam
        'urgency' => env('WEB_PUSH_URGENCY', 'high'),
    ],
];
