<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nama Aplikasi (untuk endpoint /ping)
    |--------------------------------------------------------------------------
    */
    'app' => env('SIBIJAK_APP_NAME', 'SIMONPOKJA (Monitoring Pokja LPSE)'),

    /*
    |--------------------------------------------------------------------------
    | Token Integrasi SIBIJAK
    |--------------------------------------------------------------------------
    | Token bearer yang dipakai aplikasi SIBIJAK untuk mengakses endpoint API.
    | Isi nilainya di .env melalui variabel SIBIJAK_API_TOKEN.
    |
    | Mendukung lebih dari satu konsumen dengan memisahkan token memakai koma,
    | contoh: SIBIJAK_API_TOKEN=token1,token2
    */
    'token' => env('SIBIJAK_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Batas pagination maksimum
    |--------------------------------------------------------------------------
    */
    'per_page_max' => 100,
];