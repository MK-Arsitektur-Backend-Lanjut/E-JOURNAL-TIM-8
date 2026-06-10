<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Document;
use App\Models\Author;

class BenchmarkPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run performance benchmarks for Index and Redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai Benchmark...");

        // 1. Ensure data
        $docCount = Document::count();
        if ($docCount < 5000) {
            $this->info("Data masih sedikit. Memulai proses Seeding data dummy... (Mohon tunggu sebentar)");
            
            for ($i = 0; $i < 5; $i++) {
                $author = Author::factory()->create();
                Document::factory(1000)->create([
                    'author_id' => $author->id,
                    'year' => rand(2010, 2026),
                ]);
            }
            $this->info("Seeding 5000 data dokumen selesai!");
        }

        // Get a target author and year to test
        $firstDoc = Document::inRandomOrder()->first();
        if (!$firstDoc) {
            $this->error("Tidak ada data di database.");
            return;
        }

        $targetAuthorId = $firstDoc->author_id;
        $targetYear = $firstDoc->year;

        $this->info("\n=== BENCHMARK: DATABASE INDEXING ===");
        
        // Before (No Index)
        $startNoIndex = microtime(true);
        // Force ignore index
        $resultsNoIndex = DB::select("SELECT * FROM documents IGNORE INDEX (idx_documents_author_year) WHERE author_id = ? AND year = ? LIMIT 1000", [$targetAuthorId, $targetYear]);
        $endNoIndex = microtime(true);
        $timeNoIndex = ($endNoIndex - $startNoIndex) * 1000;
        
        $this->line("[BEFORE] Query tanpa Index memakan waktu: " . round($timeNoIndex, 2) . " ms");

        // After (With Index)
        $startIndex = microtime(true);
        $resultsIndex = DB::select("SELECT * FROM documents WHERE author_id = ? AND year = ? LIMIT 1000", [$targetAuthorId, $targetYear]);
        $endIndex = microtime(true);
        $timeIndex = ($endIndex - $startIndex) * 1000;
        
        $this->line("[AFTER]  Query dengan Index memakan waktu: " . round($timeIndex, 2) . " ms");
        
        if ($timeIndex > 0) {
            $speedupIndex = round($timeNoIndex / $timeIndex, 1);
            $this->info("Kecepatan meningkat: {$speedupIndex}x lipat!");
        } else {
            $this->info("Waktu eksekusi sangat cepat (< 0.01 ms).");
        }

        $this->info("\n=== BENCHMARK: REDIS CACHING ===");
        
        $cacheKey = "benchmark_author_{$targetAuthorId}_year_{$targetYear}";
        
        try {
            // Ensure redis is cleared for this key
            Cache::store('redis')->forget($cacheKey);
            
            // Before (Database Hit)
            $startDb = microtime(true);
            $dataDb = Document::where('author_id', $targetAuthorId)->where('year', $targetYear)->limit(1000)->get();
            $endDb = microtime(true);
            $timeDb = ($endDb - $startDb) * 1000;
            
            $this->line("[BEFORE] Mengambil dari Database (Disk): " . round($timeDb, 2) . " ms");

            // Cache the result in Redis
            Cache::store('redis')->put($cacheKey, $dataDb, 60);

            // After (Redis Hit)
            $startRedis = microtime(true);
            $dataRedis = Cache::store('redis')->get($cacheKey);
            $endRedis = microtime(true);
            $timeRedis = ($endRedis - $startRedis) * 1000;

            $this->line("[AFTER]  Mengambil dari Redis (RAM): " . round($timeRedis, 2) . " ms");

            if ($timeRedis > 0) {
                $speedupRedis = round($timeDb / $timeRedis, 1);
                $this->info("Kecepatan meningkat: {$speedupRedis}x lipat!");
            } else {
                $this->info("Waktu eksekusi sangat cepat (< 0.01 ms).");
            }

            // 100 Users Simulation Benchmark
            $this->info("\n=== BENCHMARK: SIMULASI 100 USERS (100 HITS) ===");
            
            // 1. Tanpa Index (100 Hits)
            $start100NoIndex = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                DB::select("SELECT * FROM documents IGNORE INDEX (idx_documents_author_year) WHERE author_id = ? AND year = ? LIMIT 100", [$targetAuthorId, $targetYear]);
            }
            $time100NoIndex = (microtime(true) - $start100NoIndex) * 1000;
            $this->line("[100 Users] Tanpa Index memakan waktu: " . round($time100NoIndex, 2) . " ms");

            // 2. Dengan Index (100 Hits)
            $start100Index = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                DB::select("SELECT * FROM documents WHERE author_id = ? AND year = ? LIMIT 100", [$targetAuthorId, $targetYear]);
            }
            $time100Index = (microtime(true) - $start100Index) * 1000;
            $this->line("[100 Users] Dengan Index memakan waktu: " . round($time100Index, 2) . " ms");
            
            // 3. Redis Caching (100 Hits)
            $start100Redis = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                Cache::store('redis')->get($cacheKey);
            }
            $time100Redis = (microtime(true) - $start100Redis) * 1000;
            $this->line("[100 Users] Mengambil dari Redis (RAM): " . round($time100Redis, 2) . " ms");

            // Perbandingan
            if ($time100Index > 0) {
                $speedupIndex = round($time100NoIndex / $time100Index, 1);
                $this->info("-> Peningkatan Kecepatan Indexing: {$speedupIndex}x lipat lebih cepat!");
            }
            if ($time100Redis > 0) {
                $speedupRedis = round($time100Index / $time100Redis, 1);
                $this->info("-> Peningkatan Kecepatan Redis vs DB: {$speedupRedis}x lipat lebih cepat!");
            }
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke Redis. Pastikan server Redis Anda aktif di localhost:6379.");
            $this->error("Pesan Error: " . $e->getMessage());
        }
        
        $this->info("\nPengujian Selesai.");
    }
}
