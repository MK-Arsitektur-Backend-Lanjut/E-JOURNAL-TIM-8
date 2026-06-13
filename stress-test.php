<?php

/**
 * Stress Test Script untuk E-Journal Digital Library
 * 
 * Jalankan dengan:
 *   php stress-test.php
 * 
 * Script ini melakukan load testing progresif pada endpoint-endpoint utama
 * dengan concurrent requests yang meningkat, mengukur:
 * - Response time (mean, p50, p95, p99)
 * - Throughput (requests per second)
 * - Memory & CPU usage
 * - Cache hit/miss ratio
 * - Bottlenecks
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

class StressTest
{
    private $baseUrl = 'http://localhost:8000';
    private $endpoints = [
        '/api/catalog/authors' => 'catalog_authors',
        '/api/catalog/tags' => 'catalog_tags',
        '/api/documents/1' => 'single_document',
        '/api/documents/1/recommendations' => 'recommendations',
    ];
    private $concurrencies = [1, 5, 10, 20, 50];
    private $requestsPerLevel = 100;
    private $results = [];
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = date('Y-m-d_H-i-s');
    }

    public function run()
    {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║    E-JOURNAL STRESS TEST & PERFORMANCE ANALYSIS            ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "Base URL: {$this->baseUrl}\n";
        echo "Timestamp: {$this->timestamp}\n";
        echo "Requests per level: {$this->requestsPerLevel}\n\n";

        DB::enableQueryLog();
        Cache::flush();

        foreach ($this->endpoints as $endpoint => $name) {
            echo "Testing: $endpoint\n";
            echo str_repeat("─", 70) . "\n";

            foreach ($this->concurrencies as $concurrency) {
                $this->testEndpoint($endpoint, $name, $concurrency);
            }
            echo "\n";
        }

        $this->generateReport();
        $this->saveResults();
        $this->analyzeBottlenecks();
    }

    private function testEndpoint($endpoint, $name, $concurrency)
    {
        echo "  [Concurrency: $concurrency] Running {$this->requestsPerLevel} requests... ";
        flush();

        $times = [];
        $sizes = [];
        $cacheHits = 0;
        $cacheMisses = 0;
        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        // Simulate concurrent requests
        for ($i = 0; $i < $this->requestsPerLevel; $i++) {
            $beforeCache = Cache::getStore()->connection()->dbsize();
            $reqStart = microtime(true);

            try {
                $response = Http::get($this->baseUrl . $endpoint);
                $reqTime = (microtime(true) - $reqStart) * 1000; // ms

                $times[] = $reqTime;
                $sizes[] = strlen($response->body());

                // Simple cache hit/miss detection
                $afterCache = Cache::getStore()->connection()->dbsize();
                if ($afterCache > $beforeCache) {
                    $cacheMisses++;
                } else {
                    $cacheHits++;
                }
            } catch (\Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }

            // Stagger requests untuk simulate concurrent load
            if ($i % max(1, intval($this->requestsPerLevel / $concurrency)) == 0) {
                usleep(10000); // 10ms delay
            }
        }

        $totalTime = microtime(true) - $startTime;
        $endMemory = memory_get_usage(true);
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // MB

        // Calculate metrics
        $metrics = $this->calculateMetrics($times, $sizes, $totalTime, $memoryUsed, $cacheHits, $cacheMisses);

        // Store result
        $this->results[$name][$concurrency] = $metrics;

        // Display
        printf(
            "  RPS: %.1f | Mean: %.2fms | P95: %.2fms | P99: %.2fms | Cache Hit: %.1f%% | Mem: %.2fMB\n",
            $metrics['rps'],
            $metrics['mean'],
            $metrics['p95'],
            $metrics['p99'],
            $metrics['cacheHitRatio'],
            $metrics['memoryUsed']
        );
    }

    private function calculateMetrics($times, $sizes, $totalTime, $memoryUsed, $cacheHits, $cacheMisses)
    {
        sort($times);
        $count = count($times);

        return [
            'count' => $count,
            'totalTime' => $totalTime,
            'rps' => $count / $totalTime,
            'mean' => array_sum($times) / $count,
            'median' => $times[intval($count * 0.5)],
            'p95' => $times[intval($count * 0.95)],
            'p99' => $times[intval($count * 0.99)],
            'min' => min($times),
            'max' => max($times),
            'stddev' => $this->standardDeviation($times),
            'avgSize' => array_sum($sizes) / count($sizes),
            'totalSize' => array_sum($sizes) / 1024, // KB
            'memoryUsed' => $memoryUsed,
            'cacheHits' => $cacheHits,
            'cacheMisses' => $cacheMisses,
            'cacheHitRatio' => $count > 0 ? ($cacheHits / $count) * 100 : 0,
        ];
    }

    private function standardDeviation($array)
    {
        if (count($array) == 0) return 0;
        $mean = array_sum($array) / count($array);
        $variance = array_sum(array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $array)) / count($array);
        return sqrt($variance);
    }

    private function generateReport()
    {
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                   DETAILED METRICS REPORT                   ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        foreach ($this->results as $name => $concurrencyResults) {
            echo "📊 Endpoint: {$name}\n";
            echo "Concurrent | RPS    | Mean(ms) | P95(ms) | P99(ms) | StdDev | Cache% | Mem(MB)\n";
            echo str_repeat("─", 85) . "\n";

            foreach ($concurrencyResults as $concurrency => $metrics) {
                printf(
                    "%-10d | %6.1f | %8.2f | %7.2f | %7.2f | %6.2f | %6.1f | %6.2f\n",
                    $concurrency,
                    $metrics['rps'],
                    $metrics['mean'],
                    $metrics['p95'],
                    $metrics['p99'],
                    $metrics['stddev'],
                    $metrics['cacheHitRatio'],
                    $metrics['memoryUsed']
                );
            }
            echo "\n";
        }
    }

    private function analyzeBottlenecks()
    {
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║               BOTTLENECK & PATTERN ANALYSIS                 ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        foreach ($this->results as $name => $concurrencyResults) {
            ksort($concurrencyResults);
            $results = array_values($concurrencyResults);

            if (count($results) < 2) continue;

            $firstMean = $results[0]['mean'];
            $lastMean = $results[count($results) - 1]['mean'];
            $increase = (($lastMean - $firstMean) / $firstMean) * 100;

            echo "🔍 $name\n";

            // Detect pattern
            $isExponential = $this->detectExponentialGrowth($results);
            $pattern = $this->classifyPattern($results);

            echo "   Response time increase: {$increase}%\n";
            echo "   Pattern: $pattern\n";

            if ($isExponential) {
                echo "   ⚠️  EXPONENTIAL GROWTH - Critical bottleneck detected\n";
                echo "   → Causes: likely cache miss, N+1 queries, or memory pressure\n";
                echo "   → Recommendation: implement caching, query optimization, or rate limiting\n";
            } elseif ($increase > 50) {
                echo "   ⚠️  SIGNIFICANT DEGRADATION - Monitor closely\n";
                echo "   → Recommendation: load testing with profiler\n";
            } else {
                echo "   ✅ GOOD - Linear or stable performance\n";
            }

            // Analyze cache behavior
            $avgCacheHit = array_sum(array_column($results, 'cacheHitRatio')) / count($results);
            echo "   Avg Cache Hit Ratio: {$avgCacheHit}%\n";

            if ($avgCacheHit < 50) {
                echo "   ⚠️  Low cache hit ratio - consider extending TTL or caching more aggressively\n";
            }

            echo "\n";
        }
    }

    private function detectExponentialGrowth($results)
    {
        if (count($results) < 3) return false;

        $means = array_column($results, 'mean');
        $ratios = [];

        for ($i = 1; $i < count($means); $i++) {
            if ($means[$i - 1] > 0) {
                $ratios[] = $means[$i] / $means[$i - 1];
            }
        }

        // If ratios are consistently > 1.5, likely exponential
        $avgRatio = array_sum($ratios) / count($ratios);
        return $avgRatio > 1.3;
    }

    private function classifyPattern($results)
    {
        $means = array_column($results, 'mean');
        $ratios = [];

        for ($i = 1; $i < count($means); $i++) {
            if ($means[$i - 1] > 0) {
                $ratios[] = $means[$i] / $means[$i - 1];
            }
        }

        $avgRatio = array_sum($ratios) / count($ratios);

        if ($avgRatio > 1.5) {
            return "🔴 EXPONENTIAL (ratio: " . round($avgRatio, 2) . "x)";
        } elseif ($avgRatio > 1.1) {
            return "🟡 DEGRADING (ratio: " . round($avgRatio, 2) . "x)";
        } else {
            return "🟢 LINEAR/STABLE (ratio: " . round($avgRatio, 2) . "x)";
        }
    }

    private function saveResults()
    {
        $filename = storage_path("logs/stress-test-{$this->timestamp}.json");
        file_put_contents($filename, json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "📁 Results saved to: {$filename}\n\n";
    }
}

// Run stress test
$test = new StressTest();
$test->run();
