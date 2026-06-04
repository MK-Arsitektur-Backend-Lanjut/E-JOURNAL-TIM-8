<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Total target data: ~10.000+ records subscriptions
     *
     * Distribusi skenario testing:
     * ┌─────────────────────┬──────────┬─────────────────────────────────────────┐
     * │ Skenario            │ Jumlah   │ isValidForDownload() Result             │
     * ├─────────────────────┼──────────┼─────────────────────────────────────────┤
     * │ Active (monthly)    │ 3.000    │ ✅ TRUE  — boleh unduh                  │
     * │ Active (yearly)     │ 1.500    │ ✅ TRUE  — boleh unduh                  │
     * │ Active (trial)      │ 500      │ ✅ TRUE  — boleh unduh                  │
     * │ Lifetime            │ 200      │ ✅ TRUE  — boleh unduh (tanpa batas)    │
     * │ Expired             │ 3.000    │ ❌ FALSE — tidak boleh unduh            │
     * │ Cancelled           │ 1.200    │ ❌ FALSE — tidak boleh unduh            │
     * │ Pending             │ 600      │ ❌ FALSE — belum aktif                  │
     * ├─────────────────────┼──────────┼─────────────────────────────────────────┤
     * │ TOTAL               │ 10.000   │                                         │
     * └─────────────────────┴──────────┴─────────────────────────────────────────┘
     */
    public function run(): void
    {
        $this->command->info('🌱 Memulai seeding subscriptions...');
        $this->command->newLine();

        // ── KELOMPOK 1: USER DENGAN LANGGANAN AKTIF (MONTHLY) ──────────────────
        // 3.000 user baru, masing-masing 1 langganan bulanan aktif
        $this->command->info('📦 [1/7] Membuat 3.000 user dengan langganan aktif (monthly)...');
        User::factory(3_000)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->active()
                    ->create();
            });

        // ── KELOMPOK 2: USER DENGAN LANGGANAN AKTIF (YEARLY) ───────────────────
        // 1.500 user dengan langganan tahunan — skenario subscriber premium
        $this->command->info('📦 [2/7] Membuat 1.500 user dengan langganan aktif (yearly)...');
        User::factory(1_500)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->yearly()
                    ->create();
            });

        // ── KELOMPOK 3: USER DENGAN LANGGANAN TRIAL ────────────────────────────
        // 500 user baru yang sedang masa percobaan
        $this->command->info('📦 [3/7] Membuat 500 user dengan langganan trial...');
        User::factory(500)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->trial()
                    ->create();
            });

        // ── KELOMPOK 4: USER DENGAN LANGGANAN LIFETIME ─────────────────────────
        // 200 user premium dengan akses seumur hidup
        $this->command->info('📦 [4/7] Membuat 200 user dengan langganan lifetime...');
        User::factory(200)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->lifetime()
                    ->create();
            });

        // ── KELOMPOK 5: USER DENGAN LANGGANAN EXPIRED ──────────────────────────
        // 3.000 user yang dulu pernah berlangganan tapi sudah habis
        // Ini adalah skenario PALING PENTING untuk testing penolakan akses
        $this->command->info('📦 [5/7] Membuat 3.000 user dengan langganan expired...');
        User::factory(3_000)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->expired()
                    ->create();
            });

        // ── KELOMPOK 6: USER DENGAN LANGGANAN DIBATALKAN ───────────────────────
        // 1.200 user yang membatalkan langganannya
        $this->command->info('📦 [6/7] Membuat 1.200 user dengan langganan cancelled...');
        User::factory(1_200)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->cancelled()
                    ->create();
            });

        // ── KELOMPOK 7: USER DENGAN LANGGANAN PENDING ──────────────────────────
        // 600 user yang sudah daftar tapi belum bayar
        $this->command->info('📦 [7/7] Membuat 600 user dengan langganan pending...');
        User::factory(600)
            ->create()
            ->each(function (User $user) {
                Subscription::factory()
                    ->for($user)
                    ->pending()
                    ->create();
            });

        // ── BONUS: USER DENGAN RIWAYAT MULTIPLE SUBSCRIPTION ──────────────────
        // Ambil 100 user yang sudah ada, tambahkan riwayat expired sebelumnya
        // untuk simulasi user yang sudah beberapa kali berlangganan
        $this->command->newLine();
        $this->command->info('🔄 Menambahkan riwayat historis ke 100 user existing...');
        User::inRandomOrder()->limit(100)->get()
            ->each(function (User $user) {
                // Tambahkan 1-3 langganan expired lama ke user ini
                $historicalCount = rand(1, 3);
                Subscription::factory($historicalCount)
                    ->for($user)
                    ->expired()
                    ->create();
            });

        // ── LAPORAN AKHIR ──────────────────────────────────────────────────────
        $this->command->newLine();
        $totalUsers         = User::count();
        $totalSubscriptions = Subscription::count();
        $totalActive        = Subscription::where('status', 'active')->count();
        $totalExpired       = Subscription::where('status', 'expired')->count();
        $totalCancelled     = Subscription::where('status', 'cancelled')->count();
        $totalPending       = Subscription::where('status', 'pending')->count();

        $this->command->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Users',              number_format($totalUsers)],
                ['Total Subscriptions',      number_format($totalSubscriptions)],
                ['─────', '─────'],
                ['✅  Aktif (boleh unduh)',  number_format($totalActive)],
                ['❌  Expired',             number_format($totalExpired)],
                ['❌  Cancelled',           number_format($totalCancelled)],
                ['⏳  Pending',             number_format($totalPending)],
            ]
        );

        $this->command->info('✅ Seeding selesai!');
    }
}
