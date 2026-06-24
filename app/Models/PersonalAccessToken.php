<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Override untuk mencegah row lock contention di database saat stress test.
     * Dengan mengosongkan method ini, Sanctum tidak akan melakukan query UPDATE
     * ke tabel personal_access_tokens pada setiap request API, sehingga request
     * menjadi benar-benar read-only dan performa meningkat tajam.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setLastUsedAtAttribute($value)
    {
        // No-op: Tidak melakukan apa-apa untuk menonaktifkan update last_used_at
    }
}
