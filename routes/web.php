<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — E-Journal
|--------------------------------------------------------------------------
| Route halaman-halaman yang di-render dengan Blade view.
| Semua menggunakan session-based auth (bukan Sanctum token).
*/

// ── Public Pages ──────────────────────────────────────────────────────────
Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/plans', [PageController::class, 'plans'])->name('plans');

// ── Auth ──────────────────────────────────────────────────────────────────
Route::get('/login', [PageController::class, 'loginForm'])->name('login');
Route::post('/login', [PageController::class, 'loginPost'])->name('login.post');
Route::post('/logout', [PageController::class, 'logout'])->name('logout');

// ── Protected Pages (memerlukan login via session) ─────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');

    // Proses berlangganan dari form web
    Route::post('/subscribe', [PageController::class, 'subscribePost'])->name('subscribe.post');

    // Batalkan langganan (Route Model Binding — Laravel inject Subscription otomatis)
    Route::patch('/subscription/{subscription}/cancel', [PageController::class, 'cancelSubscription'])->name('subscription.cancel');
});
