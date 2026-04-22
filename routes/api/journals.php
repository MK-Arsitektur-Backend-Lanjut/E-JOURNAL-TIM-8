<?php

/*
|--------------------------------------------------------------------------
| Journal Routes — Konten Jurnal & Unduhan
|--------------------------------------------------------------------------
| Semua route yang berkaitan dengan akses jurnal dan proses unduhan.
|
| Middleware Stack:
|   1. auth:sanctum          — Wajib login
|   2. subscription.access   — Wajib punya langganan aktif
|
| Jika langganan tidak aktif, middleware akan otomatis return 403
| beserta daftar paket berlangganan yang tersedia.
|
| Endpoint (akan diisi seiring pengembangan Modul Jurnal):
|   GET  /journals/download      - [DUMMY] Simulasi download jurnal
*/

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'subscription.access'])->group(function () {

    // ─────────────────────────────────────────────────────────────────────
    // DUMMY — Endpoint testing untuk verifikasi middleware subscription
    // Akan diganti dengan JournalController di Modul selanjutnya
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/journals/download', function () {
        return response()->json([
            'message' => '✅ Akses diterima! File jurnal siap diunduh.',
            'file'    => 'jurnal-ilmiah-vol-1.pdf',
            'size'    => '2.4 MB',
        ]);
    });

    // ─────────────────────────────────────────────────────────────────────
    // TODO: Modul Jurnal — Tambahkan routes di sini
    // Contoh:
    // Route::get('/journals', [JournalController::class, 'index']);
    // Route::get('/journals/{id}', [JournalController::class, 'show']);
    // Route::get('/journals/{id}/download', [JournalController::class, 'download']);
    // ─────────────────────────────────────────────────────────────────────
});
