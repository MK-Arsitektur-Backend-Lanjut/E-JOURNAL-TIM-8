<?php

namespace App\Tests;

use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Benchmark Test untuk Redis Caching Performance
 * 
 * Jalankan dengan: php artisan tinker
 * include('app/Tests/CacheBenchmark.php');
 * $bench = new CacheBenchmark();
 * $bench->runAll();
 */
class CacheBenchmark
{
    private $iterations = 100;
    private $results = [];

    /**
     * Run all benchmarks
     */
    public function runAll()
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "🚀 REDIS CACHE BENCHMARK - E-JOURNAL DIGITAL LIBRARY\n";
        echo str_repeat("=", 80) . "\n";

        // Flush cache sebelum testing
        Cache::flush();
        DB::enableQueryLog();

        $this->benchmarkCatalogQueries();
        $this->benchmarkDocumentQuery();
        $this->benchmarkRecommendationsQuery();
        $this->displayResults();

        echo "\n" . str_repeat("=", 80) . "\n";
    }

    /**
     * Benchmark: Get Authors & Tags (Catalog)
     */
    private function benchmarkCatalogQueries()
    {
        echo "\n📚 TEST 1: Catalog Queries (Authors & Tags)\n";
        echo str_repeat("-", 80) . "\n";

        // Test 1a: Authors WITHOUT cache
        Cache::flush();
        DB::flushQueryLog();
        $startTime = microtime(true);

        for ($i = 0; $i < $this->iterations; $i++) {
            Author::orderBy('name')->get(['id', 'name']);
        }

        $timeWithoutCache = (microtime(true) - $startTime) * 1000;
        $queriesWithoutCache = count(DB::getQueryLog());

        echo "  ❌ WITHOUT Cache ($this->iterations requests):\n";
        echo "     - Time: {$timeWithoutCache}ms\n";
        echo "     - Queries: $queriesWithoutCache\n";

        // Test 1b: Authors WITH cache
        Cache::flush();
        DB::flushQueryLog();
        $startTime = microtime(true);

        for ($i = 0; $i < $this->iterations; $i++) {
            CacheService::getAuthors(function () {
                return Author::orderBy('name')->get(['id', 'name']);
            });
        }

        $timeWithCache = (microtime(true) - $startTime) * 1000;
        $queriesWithCache = count(DB::getQueryLog());

        echo "  ✅ WITH Cache ($this->iterations requests):\n";
        echo "     - Time: {$timeWithCache}ms\n";
        echo "     - Queries: $queriesWithCache\n";

        $improvement = (($timeWithoutCache - $timeWithCache) / $timeWithoutCache) * 100;
        echo "  📈 Improvement: {$improvement}% faster\n";
        echo "     - Query reduction: " . ($queriesWithoutCache - $queriesWithCache) . " queries saved\n";

        $this->results['Catalog'] = [
            'without_cache' => ['time' => $timeWithoutCache, 'queries' => $queriesWithoutCache],
            'with_cache' => ['time' => $timeWithCache, 'queries' => $queriesWithCache],
            'improvement' => $improvement,
        ];
    }

    /**
     * Benchmark: Get Single Document
     */
    private function benchmarkDocumentQuery()
    {
        echo "\n📄 TEST 2: Single Document Query\n";
        echo str_repeat("-", 80) . "\n";

        $documentId = 1; // Asumsikan dokumen dengan ID 1 ada

        // Test 2a: WITHOUT cache
        Cache::flush();
        DB::flushQueryLog();
        $startTime = microtime(true);

        for ($i = 0; $i < $this->iterations; $i++) {
            Document::with(['author', 'tags'])->find($documentId);
        }

        $timeWithoutCache = (microtime(true) - $startTime) * 1000;
        $queriesWithoutCache = count(DB::getQueryLog());

        echo "  ❌ WITHOUT Cache ($this->iterations requests):\n";
        echo "     - Time: {$timeWithoutCache}ms\n";
        echo "     - Queries: $queriesWithoutCache\n";

        // Test 2b: WITH cache
        Cache::flush();
        DB::flushQueryLog();
        $startTime = microtime(true);

        for ($i = 0; $i < $this->iterations; $i++) {
            CacheService::getDocument($documentId, function () use ($documentId) {
                return Document::with(['author', 'tags'])->find($documentId);
            });
        }

        $timeWithCache = (microtime(true) - $startTime) * 1000;
        $queriesWithCache = count(DB::getQueryLog());

        echo "  ✅ WITH Cache ($this->iterations requests):\n";
        echo "     - Time: {$timeWithCache}ms\n";
        echo "     - Queries: $queriesWithCache\n";

        $improvement = (($timeWithoutCache - $timeWithCache) / $timeWithoutCache) * 100;
        echo "  📈 Improvement: {$improvement}% faster\n";
        echo "     - Query reduction: " . ($queriesWithoutCache - $queriesWithCache) . " queries saved\n";

        $this->results['Single Document'] = [
            'without_cache' => ['time' => $timeWithoutCache, 'queries' => $queriesWithoutCache],
            'with_cache' => ['time' => $timeWithCache, 'queries' => $queriesWithCache],
            'improvement' => $improvement,
        ];
    }

    /**
     * Benchmark: Recommendations (Most expensive query)
     */
    private function benchmarkRecommendationsQuery()
    {
        echo "\n🎯 TEST 3: Recommendations Query (Most Expensive)\n";
        echo str_repeat("-", 80) . "\n";

        $documentId = 1;

        // Test 3a: WITHOUT cache
        Cache::flush();
        DB::flushQueryLog();
        $startTime = microtime(true);

        for ($i = 0; $i < 10; $i++) { // Hanya 10 kali karena expensive
            if ($doc = Document::with('tags')->find($documentId)) {
                if (!$doc->tags->isEmpty()) {
                    $tagIds = $doc->tags->pluck('id')->all();
                    Document::with(['author', 'tags'])
                        ->where('id', '!=', $documentId)
                        ->join('document_tag', 'documents.id', '=', 'document_tag.document_id')
                        ->whereIn('document_tag.tag_id', $tagIds)
                        ->selectRaw('COUNT(document_tag.tag_id) as shared_tags_count')
                        ->groupBy('documents.id')
                        ->orderByDesc('shared_tags_count')
                        ->limit(5)
                        ->get();
                }
            }
        }

        $timeWithoutCache = (microtime(true) - $startTime) * 1000;
        $queriesWithoutCache = count(DB::getQueryLog());

        echo "  ❌ WITHOUT Cache (10 requests):\n";
        echo "     - Time: {$timeWithoutCache}ms\n";
        echo "     - Queries: $queriesWithoutCache\n";

        // Test 3b: WITH cache
        Cache::flush();
        DB::flushQueryLog();
        $startTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            CacheService::getRecommendations($documentId, function () use ($documentId) {
                if ($doc = Document::with('tags')->find($documentId)) {
                    if (!$doc->tags->isEmpty()) {
                        $tagIds = $doc->tags->pluck('id')->all();
                        return Document::with(['author', 'tags'])
                            ->where('id', '!=', $documentId)
                            ->join('document_tag', 'documents.id', '=', 'document_tag.document_id')
                            ->whereIn('document_tag.tag_id', $tagIds)
                            ->selectRaw('COUNT(document_tag.tag_id) as shared_tags_count')
                            ->groupBy('documents.id')
                            ->orderByDesc('shared_tags_count')
                            ->limit(5)
                            ->get();
                    }
                }
                return collect([]);
            });
        }

        $timeWithCache = (microtime(true) - $startTime) * 1000;
        $queriesWithCache = count(DB::getQueryLog());

        echo "  ✅ WITH Cache (10 requests):\n";
        echo "     - Time: {$timeWithCache}ms\n";
        echo "     - Queries: $queriesWithCache\n";

        if ($timeWithoutCache > 0) {
            $improvement = (($timeWithoutCache - $timeWithCache) / $timeWithoutCache) * 100;
            echo "  📈 Improvement: {$improvement}% faster\n";
            echo "     - Query reduction: " . ($queriesWithoutCache - $queriesWithCache) . " queries saved\n";

            $this->results['Recommendations'] = [
                'without_cache' => ['time' => $timeWithoutCache, 'queries' => $queriesWithoutCache],
                'with_cache' => ['time' => $timeWithCache, 'queries' => $queriesWithCache],
                'improvement' => $improvement,
            ];
        }
    }

    /**
     * Display summary results
     */
    private function displayResults()
    {
        echo "\n\n" . str_repeat("=", 80) . "\n";
        echo "📊 SUMMARY RESULTS\n";
        echo str_repeat("=", 80) . "\n";

        $totalImprovement = 0;
        $count = 0;

        foreach ($this->results as $test => $data) {
            echo "\n✨ {$test}:\n";
            echo "   Time Improvement: " . number_format($data['improvement'], 2) . "%\n";
            echo "   Without Cache: " . number_format($data['without_cache']['time'], 2) . "ms / " . $data['without_cache']['queries'] . " queries\n";
            echo "   With Cache: " . number_format($data['with_cache']['time'], 2) . "ms / " . $data['with_cache']['queries'] . " queries\n";
            
            $totalImprovement += $data['improvement'];
            $count++;
        }

        $avgImprovement = $count > 0 ? $totalImprovement / $count : 0;
        echo "\n" . str_repeat("-", 80) . "\n";
        echo "🎯 AVERAGE IMPROVEMENT: " . number_format($avgImprovement, 2) . "%\n";
        echo str_repeat("=", 80) . "\n\n";
    }
}
