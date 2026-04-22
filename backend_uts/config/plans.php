<?php

/**
 * Konfigurasi paket langganan E-Journal.
 *
 * Dipusatkan di sini agar tidak tersebar di berbagai file (Controller,
 * Validation, Factory, dsb.). Jika ingin ubah durasi atau tambah paket baru,
 * cukup di sini — tidak perlu sentuh kode lain.
 *
 * Penggunaan: config('plans.available'), config('plans.durations.monthly')
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Daftar Nama Paket yang Valid
    |--------------------------------------------------------------------------
    | Digunakan untuk validasi input di Form Request.
    */
    'available' => ['trial', 'monthly', 'yearly'],

    /*
    |--------------------------------------------------------------------------
    | Durasi Setiap Paket (dalam hari)
    |--------------------------------------------------------------------------
    | NULL = lifetime (tidak ada batas waktu, expires_at akan di-set NULL).
    */
    'durations' => [
        'trial'    => 7,
        'monthly'  => 30,
        'yearly'   => 365,
        'lifetime' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | TTL Cache Validasi Langganan (dalam menit)
    |--------------------------------------------------------------------------
    | Hasil isValidForDownload() di-cache selama durasi ini.
    | Sesuaikan berdasarkan kebutuhan konsistensi vs performa.
    | Nilai kecil = lebih real-time, nilai besar = lebih ringan ke DB.
    */
    'cache_ttl_minutes' => 5,

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Khusus Docker
    |--------------------------------------------------------------------------
    | Cache key prefix agar tidak bentrok jika ada beberapa environment
    | yang menggunakan Redis yang sama.
    */
    'cache_prefix' => env('APP_ENV', 'production') . '.subscription',

    /*
    |--------------------------------------------------------------------------
    | Detail Paket untuk Ditampilkan ke User
    |--------------------------------------------------------------------------
    | Digunakan pada response 403 middleware agar user tahu pilihan yang ada.
    | Frontend bisa langsung render dari data ini tanpa hardcode.
    */
    'details' => [
        'trial' => [
            'label'       => 'Trial',
            'duration'    => '7 hari',
            'description' => 'Coba gratis selama 7 hari. Akses semua jurnal.',
            'highlight'   => false,
        ],
        'monthly' => [
            'label'       => 'Monthly',
            'duration'    => '30 hari',
            'description' => 'Akses penuh selama 1 bulan. Cocok untuk penelitian jangka pendek.',
            'highlight'   => false,
        ],
        'yearly' => [
            'label'       => 'Yearly',
            'duration'    => '365 hari',
            'description' => 'Akses penuh 1 tahun. Rekomendasi untuk akademisi aktif.',
            'highlight'   => true,
        ],
    ],

];
