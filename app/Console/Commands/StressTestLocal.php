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
    protected $signature = 'stress-test:run {--users= : Jumlah virtual users spesifik (misal: 1000)}';

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
        ini_set('memory_limit', '-1');
        $this->info("=== MEMULAI SIMULASI STRESS TEST INTERNAL ===");
        $this->warn("Catatan: Pastikan server lokal Anda berjalan di terminal lain (php artisan serve).");
        
        // Fokus ke Endpoint Pencarian (Modul 2)
        // Menargetkan Laravel Octane / RoadRunner yang berjalan di port 8000 dengan query acak
        
        $userOption = $this->option('users');
        $loads = $userOption ? [(int) $userOption] : [10, 50, 100, 200, 500, 1000]; // Skenario jumlah user bersamaan
        
        $this->info("URL Target (Laravel Octane): http://127.0.0.1:8000/api/v1/documents/search (dengan filter pencarian acak)\n");

        foreach ($loads as $concurrentUsers) {
            $this->line("Menyiapkan serangan dari {$concurrentUsers} Virtual Users bersamaan...");
            
            $startTime = microtime(true);
            
            // Menggunakan Guzzle Asynchronous Pool via Laravel Http Client
            $responses = Http::pool(function (Pool $pool) use ($concurrentUsers) {
                $requests = [];
                
                // Variasi parameter untuk simulasi pencarian acak realistik
                $years = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];
                $alphabets = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'v', 'w', 'y', 'z'];
                $keywords = ['analysis', 'system', 'journal', 'data', 'design', 'web', 'framework', 'database'];
                
                for ($i = 0; $i < $concurrentUsers; $i++) {
                    $params = [];
                    $type = rand(1, 6);
                    
                    if ($type === 1) {
                        $params['year'] = $years[array_rand($years)];
                    } elseif ($type === 2) {
                        $params['title'] = $alphabets[array_rand($alphabets)];
                    } elseif ($type === 3) {
                        $params['author'] = $alphabets[array_rand($alphabets)];
                    } elseif ($type === 4) {
                        $params['abstract'] = $keywords[array_rand($keywords)];
                    } elseif ($type === 5) {
                        $params['year'] = $years[array_rand($years)];
                        $params['title'] = $alphabets[array_rand($alphabets)];
                    } else {
                        // Request default tanpa filter
                        $params = [];
                    }
                    
                    $queryStr = http_build_query($params);
                    $dynamicUrl = $queryStr ? "http://127.0.0.1:8000/api/v1/documents/search?{$queryStr}" : "http://127.0.0.1:8000/api/v1/documents/search";
                    
                    $requests[] = $pool->get($dynamicUrl);
                }
                return $requests;
            });
            
            $endTime = microtime(true);
            
            $successCount = 0;
            $failCount = 0;
            $totalBytes = 0;
            $errors = [];
            
            foreach ($responses as $response) {
                if ($response instanceof \Exception) {
                    $failCount++;
                    $errName = get_class($response) . ': ' . substr($response->getMessage(), 0, 100);
                    $errors[$errName] = ($errors[$errName] ?? 0) + 1;
                } elseif (!$response->successful()) {
                    $failCount++;
                    $errName = "HTTP Status " . $response->status();
                    $errors[$errName] = ($errors[$errName] ?? 0) + 1;
                } else {
                    $successCount++;
                    // Calculate size of response body
                    $totalBytes += strlen($response->body());
                }
            }
            
            $totalTimeMs = round(($endTime - $startTime) * 1000, 2);
            $averageTimeMs = round($totalTimeMs / $concurrentUsers, 2);
            
            $totalKb = round($totalBytes / 1024, 2);
            $totalMb = round($totalBytes / (1024 * 1024), 2);
            $averageKb = $successCount > 0 ? round(($totalBytes / $successCount) / 1024, 2) : 0;
            
            $payloadInfo = "Total Payload: {$totalKb} KB ({$totalMb} MB) | Rerata Payload/User: {$averageKb} KB";
            
            $this->info("[HASIL] Beban: {$concurrentUsers} Users | Total Waktu: {$totalTimeMs} ms | Rata-rata per User: {$averageTimeMs} ms | Sukses: {$successCount} | Gagal: {$failCount}");
            $this->comment("        {$payloadInfo}");
            
            if ($failCount > 0) {
                $this->error("        Detail Error:");
                foreach ($errors as $errorMsg => $count) {
                    $this->error("        - {$errorMsg} ({$count}x)");
                }
            }
            $this->line("");
            
            // Jeda sejenak agar server bernapas sebelum ronde berikutnya
            sleep(2);
        }
        
        $this->info("Pengujian Selesai! Gunakan angka Total Waktu di atas untuk menggambar grafik di Excel.");
    }
}
