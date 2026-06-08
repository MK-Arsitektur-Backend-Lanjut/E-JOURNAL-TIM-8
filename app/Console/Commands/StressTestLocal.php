<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class StressTestLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stress-test:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate load testing (concurrent users) directly from Laravel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== MEMULAI SIMULASI STRESS TEST INTERNAL ===");
        $this->warn("Catatan: Pastikan server lokal Anda berjalan di terminal lain (php artisan serve).");
        
        // Fokus ke Endpoint Pencarian (Modul 2)
        // URL diubah menyesuaikan Laravel Herd dan prefix api/v1
        $url = 'http://e-journal-tim-8.test/api/v1/documents/search?year=2023'; 
        
        $loads = [10, 50, 100, 200, 500, 1000]; // Skenario jumlah user bersamaan
        
        $this->info("URL Target (Laravel Herd): $url\n");

        foreach ($loads as $concurrentUsers) {
            $this->line("Menyiapkan serangan dari {$concurrentUsers} Virtual Users bersamaan...");
            
            $startTime = microtime(true);
            
            // Menggunakan Guzzle Asynchronous Pool via Laravel Http Client
            $responses = Http::pool(function (Pool $pool) use ($concurrentUsers, $url) {
                $requests = [];
                for ($i = 0; $i < $concurrentUsers; $i++) {
                    $requests[] = $pool->get($url);
                }
                return $requests;
            });
            
            $endTime = microtime(true);
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($responses as $response) {
                if ($response instanceof \Exception || !$response->successful()) {
                    $failCount++;
                } else {
                    $successCount++;
                }
            }
            
            $totalTimeMs = round(($endTime - $startTime) * 1000, 2);
            $averageTimeMs = round($totalTimeMs / $concurrentUsers, 2);
            
            $this->info("[HASIL] Beban: {$concurrentUsers} Users | Total Waktu: {$totalTimeMs} ms | Rata-rata per User: {$averageTimeMs} ms | Sukses: {$successCount} | Gagal: {$failCount}\n");
            
            // Jeda sejenak agar server bernapas sebelum ronde berikutnya
            sleep(2);
        }
        
        $this->info("Pengujian Selesai! Gunakan angka Total Waktu di atas untuk menggambar grafik di Excel.");
    }
}
