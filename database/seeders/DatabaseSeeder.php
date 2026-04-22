<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // --- DATA DARI MODUL 3 (Sistem Langganan) ---

        // 1. Admin / Test user
        $testUser = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. User dengan langganan AKTIF
        $activeUser = User::factory()->create([
            'name'  => 'Active Subscriber',
            'email' => 'active@example.com',
        ]);
        Subscription::factory()->for($activeUser)->active()->create();

        // 3. User dengan langganan EXPIRED
        $expiredUser = User::factory()->create([
            'name'  => 'Expired Subscriber',
            'email' => 'expired@example.com',
        ]);
        Subscription::factory()->for($expiredUser)->expired()->create();

        // 4. User dengan langganan LIFETIME
        $lifetimeUser = User::factory()->create([
            'name'  => 'Lifetime Subscriber',
            'email' => 'lifetime@example.com',
        ]);
        Subscription::factory()->for($lifetimeUser)->lifetime()->create();

        // 5. User tanpa langganan
        User::factory()->create([
            'name'  => 'No Subscription User',
            'email' => 'nosub@example.com',
        ]);

        $this->call([
            AuthorSeeder::class,
            TagSeeder::class,
            DocumentSeeder::class,
            SubscriptionSeeder::class,
        ]);
    }
}
