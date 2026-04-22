<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdvancedSearchController;
use App\Http\Controllers\CatalogLookupController;
use App\Http\Controllers\DocumentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/authors', [CatalogLookupController::class, 'authors']);
    Route::get('/tags', [CatalogLookupController::class, 'tags']);
    Route::get('/documents/search', [AdvancedSearchController::class, 'search']);
    Route::get('/documents/{id}/recommendations', [AdvancedSearchController::class, 'recommendations']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::put('/documents/{id}', [DocumentController::class, 'update']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
});
