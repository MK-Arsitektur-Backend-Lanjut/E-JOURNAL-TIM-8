<?php

return [
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
