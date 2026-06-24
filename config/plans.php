<?php

return [
    // Toggle: aktifkan/nonaktifkan Redis cache untuk subscription
    'cache_enabled'     => env('SUBSCRIPTION_CACHE_ENABLED', true),
    'cache_prefix'      => env('SUBSCRIPTION_CACHE_PREFIX', 'subscription'),
    'cache_ttl_minutes' => env('SUBSCRIPTION_CACHE_TTL', 5),

    'available' => [
        'monthly',
        'yearly',
        'lifetime',
    ],

    'durations' => [
        'monthly' => 30,
        'yearly' => 365,
        'lifetime' => null,
    ],

    'details' => [
        'monthly' => [
            'name' => 'Monthly Plan',
            'price' => 50000,
            'description' => 'Akses penuh ke semua jurnal selama 1 bulan.',
        ],
        'yearly' => [
            'name' => 'Yearly Plan',
            'price' => 500000,
            'description' => 'Akses penuh ke semua jurnal selama 1 tahun (Hemat 2 bulan).',
        ],
        'lifetime' => [
            'name' => 'Lifetime Access',
            'price' => 2000000,
            'description' => 'Akses penuh selamanya tanpa biaya perpanjangan.',
        ],
    ],
];
