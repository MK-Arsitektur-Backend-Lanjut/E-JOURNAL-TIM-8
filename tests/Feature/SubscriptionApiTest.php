<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Bersihkan cache sebelum setiap test
        Cache::flush();
    }

    public function test_cache_is_written_on_access_and_invalidated_on_cancel(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'status' => SubscriptionStatus::Active,
            'started_at' => now()->subDay(),
            'expires_at' => now()->addDays(29),
        ]);

        Sanctum::actingAs($user);

        // 1. Verifikasi cache kosong sebelum akses
        $this->assertFalse(Cache::has("subscription.user.{$user->id}.valid"));
        $this->assertFalse(Cache::has("subscription.user.{$user->id}.active"));

        // 2. Jalankan request check download access
        $response = $this->getJson('/api/membership/download-access');
        $response->assertOk()
            ->assertJsonPath('allowed', true);

        // 3. Verifikasi cache diisi oleh CachedSubscriptionRepository
        $this->assertTrue(Cache::has("subscription.user.{$user->id}.valid"));
        $this->assertTrue(Cache::has("subscription.user.{$user->id}.active"));

        // 4. Batalkan langganan (Cancel) -> Harus memicu invalidasi cache
        $cancelResponse = $this->patchJson("/api/membership/{$subscription->id}/cancel");
        $cancelResponse->assertOk()
            ->assertJsonPath('message', 'Langganan berhasil dibatalkan.');

        // 5. Verifikasi cache user dihapus secara instan
        $this->assertFalse(Cache::has("subscription.user.{$user->id}.valid"));
        $this->assertFalse(Cache::has("subscription.user.{$user->id}.active"));
    }

    public function test_cache_is_invalidated_on_extend(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'status' => SubscriptionStatus::Active,
            'started_at' => now()->subDay(),
            'expires_at' => now()->addDays(29),
        ]);

        Sanctum::actingAs($user);

        // Buat cache dengan check access
        $this->getJson('/api/membership/download-access')->assertOk();
        $this->assertTrue(Cache::has("subscription.user.{$user->id}.valid"));

        // Perpanjang langganan (Admin / Service Action) -> Harus memicu invalidasi cache
        $extendResponse = $this->patchJson("/api/membership/{$subscription->id}/extend", [
            'days' => 10,
        ]);
        $extendResponse->assertOk();

        // Verifikasi cache dibersihkan
        $this->assertFalse(Cache::has("subscription.user.{$user->id}.valid"));
    }

    public function test_expire_overdue_command_updates_database_and_clears_cache(): void
    {
        $user1 = User::factory()->create();
        $subscription1 = Subscription::factory()->create([
            'user_id' => $user1->id,
            'plan' => 'monthly',
            'status' => SubscriptionStatus::Active,
            'started_at' => now()->subDays(40),
            'expires_at' => now()->subDays(10), // Sudah expired
        ]);

        $user2 = User::factory()->create();
        $subscription2 = Subscription::factory()->create([
            'user_id' => $user2->id,
            'plan' => 'monthly',
            'status' => SubscriptionStatus::Active,
            'started_at' => now()->subDay(),
            'expires_at' => now()->addDays(29), // Masih aktif
        ]);

        // Cek access untuk buat cache (user1 expired -> 403 Forbidden)
        Sanctum::actingAs($user1);
        $this->getJson('/api/membership/download-access')->assertStatus(403);
        $this->assertTrue(Cache::has("subscription.user.{$user1->id}.valid"));

        Sanctum::actingAs($user2);
        $this->getJson('/api/membership/download-access')->assertOk();
        $this->assertTrue(Cache::has("subscription.user.{$user2->id}.valid"));

        // Jalankan scheduler command
        $this->artisan('subscriptions:expire')
            ->assertExitCode(0);

        // Verifikasi database: User 1 expired, User 2 tetap aktif
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription1->id,
            'status' => SubscriptionStatus::Expired,
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription2->id,
            'status' => SubscriptionStatus::Active,
        ]);

        // Verifikasi cache: Cache User 1 dihapus, cache User 2 tetap ada
        $this->assertFalse(Cache::has("subscription.user.{$user1->id}.valid"));
        $this->assertTrue(Cache::has("subscription.user.{$user2->id}.valid"));
    }
}
