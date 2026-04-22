<?php

use App\Http\Middleware\CheckSubscriptionAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscription.access' => CheckSubscriptionAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // 403 JSON — saat Policy menolak akses (cancel/extend milik orang lain)
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.',
                ], 403);
            }
        });

        // 404 JSON — saat route model binding gagal ({subscription} ID tidak ditemukan)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }
        });

        // 404 JSON — saat URL route tidak ada sama sekali
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Endpoint tidak ditemukan. Periksa URL dan method yang digunakan.',
                ], 404);
            }
        });

    })->create();
