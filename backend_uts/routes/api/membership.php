<?php

/*
|--------------------------------------------------------------------------
| Membership Routes — Manajemen Langganan
|--------------------------------------------------------------------------
| Semua route yang berkaitan dengan subscription/membership user.
| Semua endpoint memerlukan autentikasi (auth:sanctum).
|
| Endpoint:
|   GET    /membership/download-access        - Cek apakah boleh download
|   GET    /membership/history                - Riwayat langganan
|   POST   /membership/subscribe              - Buat/perbarui langganan
|   PATCH  /membership/{subscription}/cancel  - Batalkan langganan
|   PATCH  /membership/{subscription}/extend  - Perpanjang langganan (admin)
*/

use App\Http\Controllers\MembershipController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('membership')->group(function () {

    // Cek akses download (dipakai frontend sebelum tampilkan tombol download)
    Route::get('/download-access', [MembershipController::class, 'checkDownloadAccess']);

    // Riwayat semua langganan milik user
    Route::get('/history', [MembershipController::class, 'history']);

    // Buat langganan baru (logika: buat baru / extend / tolak jika paket beda)
    Route::post('/subscribe', [MembershipController::class, 'store']);

    // Aksi pada langganan tertentu (Policy memastikan hanya milik sendiri)
    Route::patch('/{subscription}/cancel', [MembershipController::class, 'cancel']);
    Route::patch('/{subscription}/extend', [MembershipController::class, 'extend']);
});
