<?php

namespace Database\Seeders;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── AKUN TETAP UNTUK DEVELOPMENT & MANUAL TESTING ──────────────────────

        // 1. Admin / Test user — selalu ada, mudah diingat
        $testUser = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. User dengan langganan AKTIF — untuk testing akses unduhan berhasil
        $activeUser = User::factory()->create([
            'name'  => 'Active Subscriber',
            'email' => 'active@example.com',
        ]);
        Subscription::factory()->for($activeUser)->active()->create();

        // 3. User dengan langganan EXPIRED — untuk testing penolakan akses
        $expiredUser = User::factory()->create([
            'name'  => 'Expired Subscriber',
            'email' => 'expired@example.com',
        ]);
        Subscription::factory()->for($expiredUser)->expired()->create();

        // 4. User dengan langganan LIFETIME — untuk testing akses tanpa batas
        $lifetimeUser = User::factory()->create([
            'name'  => 'Lifetime Subscriber',
            'email' => 'lifetime@example.com',
        ]);
        Subscription::factory()->for($lifetimeUser)->lifetime()->create();

        // 5. User tanpa langganan sama sekali — untuk testing edge case
        User::factory()->create([
            'name'  => 'No Subscription User',
            'email' => 'nosub@example.com',
        ]);

        $this->command->info('✅ Akun development siap (test, active, expired, lifetime, nosub).');
        $this->command->newLine();

        // ── DATA DUMMY MASSAL (10.000+ records) ────────────────────────────────
        $this->call(SubscriptionSeeder::class);
    }
}

