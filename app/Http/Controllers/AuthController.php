<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AuthController — Sangat tipis.
 *
 * Tugas controller ini hanya 3 hal:
 * 1. Terima HTTP Request
 * 2. Panggil AuthService (yang pegang semua logika)
 * 3. Kembalikan HTTP Response (JSON)
 *
 * Tidak ada logika bisnis, tidak ada query DB, tidak ada Hash::check().
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * POST /api/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithToken(
            $request->email,
            $request->password
        );

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 401);
        }

        return response()->json([
            'message' => $result['message'],
            'token'   => $result['token'],
            'user'    => [
                'id'    => $result['user']->id,
                'name'  => $result['user']->name,
                'email' => $result['user']->email,
            ],
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->revokeCurrentToken($request->user());

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        $profile = $this->authService->getProfile($request->user());

        return response()->json($profile);
    }
}
