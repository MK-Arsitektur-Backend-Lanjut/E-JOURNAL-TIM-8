<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdvancedSearchController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/documents/search', [AdvancedSearchController::class, 'search']);
    Route::get('/documents/{id}/recommendations', [AdvancedSearchController::class, 'recommendations']);
});
