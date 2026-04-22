<?php

/*
|--------------------------------------------------------------------------
| Auth Routes — Autentikasi & Identitas User
|--------------------------------------------------------------------------
| Semua route yang berkaitan dengan login, logout, dan profil user.
| Tidak memerlukan authentication kecuali logout dan me.
*/

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Login — dapatkan token Sanctum
Route::post('/login', [AuthController::class, 'login']);

// Logout & profil — butuh login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
