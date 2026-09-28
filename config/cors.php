<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    | Dikunci pada prefiks /api agar SIBIJAK (aplikasi konsumen di origin lain)
    | dapat memanggil API dari browser. Atur origin yang diizinkan lewat variabel
    | CORS_ALLOWED_ORIGINS di .env (pisahkan dengan koma), contoh:
    |
    |   CORS_ALLOWED_ORIGINS=http://127.0.0.1:8086,http://localhost:8086
    |
    | Nilai default "*" (semua origin) hanya untuk pengembangan lokal.
    */
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];