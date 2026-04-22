<?php

/*
|--------------------------------------------------------------------------
| API Route Loader — E-Journal
|--------------------------------------------------------------------------
| File ini hanya bertugas memuat (load) file route dari setiap modul.
| JANGAN taruh logika apapun di sini.
|
| Struktur:
|   routes/api/
|   ├── auth.php        — Login, Logout, Me
|   ├── membership.php  — Subscription management
|   └── journals.php    — Akses jurnal & unduhan (butuh langganan)
|
| Untuk menambah modul baru, buat file baru di routes/api/
| lalu daftarkan dengan require di bawah ini.
*/

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/membership.php';
require __DIR__ . '/api/journals.php';
require __DIR__ . '/api/documents.php';
