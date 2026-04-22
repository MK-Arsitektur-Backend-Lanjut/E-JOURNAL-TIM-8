<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AdvancedSearchController;
use App\Http\Controllers\CatalogLookupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Document & Search Routes (Old Modules)
|--------------------------------------------------------------------------
| Menghubungkan modul lama (Pencarian & Katalog) ke sistem baru.
| Semua route di sini diproteksi oleh Sanctum.
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('v1')->group(function () {
        // Lookups (Authors & Tags)
        Route::get('authors', [CatalogLookupController::class, 'authors'])->name('api.authors');
        Route::get('tags', [CatalogLookupController::class, 'tags'])->name('api.tags');

        // Fitur Pencarian Lanjutan
        Route::get('documents/search', [AdvancedSearchController::class, 'search'])->name('api.search');

        // CRUD Dokumen/Jurnal
        Route::apiResource('documents', DocumentController::class);
        
        // Rekomendasi Jurnal
        Route::get('documents/{id}/recommendations', [AdvancedSearchController::class, 'recommendations'])->name('api.recommendations');
    });
});
