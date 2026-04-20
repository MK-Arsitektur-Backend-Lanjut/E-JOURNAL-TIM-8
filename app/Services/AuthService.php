<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * AuthService — Semua business logic autentikasi Sanctum.
 *
 * Controller tidak perlu tahu cara:
 * - Mencari user berdasarkan email
 * - Memverifikasi password
 * - Mengelola token Sanctum (revoke & buat baru)
 *
 * Semua itu ada di sini. Controller hanya panggil method ini
 * dan langsung format hasilnya sebagai HTTP response.
 */
class AuthService
{
    /**
     * Autentikasi user dan buat token Sanctum baru.
     *
     * Alur:
     * 1. Cari user berdasarkan email
     * 2. Verifikasi password
     * 3. Revoke semua token lama (satu perangkat satu token)
     * 4. Buat token baru
     *
     * @return array{success: bool, token: ?string, user: ?User, message: string}
     */
    public function loginWithToken(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'token'   => null,
                'user'    => null,
                'message' => 'Email atau password salah.',
            ];
        }

        // Revoke semua token lama — satu akun, satu token aktif
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'success' => true,
            'token'   => $token,
            'user'    => $user,
            'message' => 'Login berhasil.',
        ];
    }

    /**
     * Revoke token aktif user yang sedang login.
     *
     * @return array{message: string}
     */
    public function revokeCurrentToken(User $user): array
    {
        $user->currentAccessToken()->delete();

        return [
            'message' => 'Logout berhasil.',
        ];
    }

    /**
     * Ambil data profil user untuk endpoint /me.
     *
     * @return array{id: int, name: string, email: string, has_active_subscription: bool}
     */
    public function getProfile(User $user): array
    {
        return [
            'id'                      => $user->id,
            'name'                    => $user->name,
            'email'                   => $user->email,
            'has_active_subscription' => $user->activeSubscription !== null,
        ];
    }
}
