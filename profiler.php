<?php

/**
 * Advanced Profiling & Event Analysis
 * 
 * Mendeteksi:
 * - Slow queries (N+1, missing indexes)
 * - Cache inefficiencies
 * - Memory leaks
 * - Event bottlenecks
 * 
 * Jalankan:
 *   php profiler.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$app = require_once __DIR__ . '/bootstrap/app.php';

class AdvancedProfiler
{
    private $baseUrl = 'http://localhost:8000';
    private $events = [];
    private $queries = [];
    private $cacheEvents = [];
    private $memorySnapshots = [];

    public function __construct()
    {
        DB::enableQueryLog();
        $this->setupListeners();
    }

    private function setupListeners()
    {
        // Listen to database queries
        DB::listen(function ($query) {
            $this->events[] = [
                'type' => 'query',
                'time' => microtime(true),
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'duration' => $query->time,
            ];
        });

        // Listen to cache operations (if using events)
        Cache::listen(function ($event) {
            $this->cacheEvents[] = [
                'type' => 'cache_event',
                'event' => $event,
                'time' => microtime(true),
            ];
        });
    }

    public function profileEndpoint($url, $name)
    {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║          PROFILING: $name\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";

        $this->events = [];
        $this->queries = [];
        $this->cacheEvents = [];

        // Clear logs
        DB::flushQueryLog();

        // Memory before
        gc_collect_cycles();
        $memStart = memory_get_usage(true) / 1024 / 1024;
        $timeStart = microtime(true);

        // Make request
        $response = Http::get($this->baseUrl . $url);
        
        // Metrics after
        $timeEnd = microtime(true);
        $memEnd = memory_get_usage(true) / 1024 / 1024;
        
        $elapsedTime = ($timeEnd - $timeStart) * 1000; // ms
        $memUsed = $memEnd - $memStart;

        // Get query log
        $queries = DB::getQueryLog();

        echo "\n📊 REQUEST METRICS\n";
        echo "─────────────────────────────────────\n";
        printf("  Response Time: %.2fms\n", $elapsedTime);
        printf("  Memory Used: %.2fMB\n", $memUsed);
        printf("  HTTP Status: %d\n", $response->status());
        printf("  Response Size: %.2fKB\n", strlen($response->body()) / 1024);

        echo "\n📈 DATABASE ANALYSIS\n";
        echo "─────────────────────────────────────\n";
        printf("  Total Queries: %d\n", count($queries));
        $this->analyzeQueries($queries);

        echo "\n💾 CACHE ANALYSIS\n";
        echo "─────────────────────────────────────\n";
        $this->analyzeCacheUsage();

        echo "\n⚠️  POTENTIAL ISSUES\n";
        echo "─────────────────────────────────────\n";
        $this->detectIssues($queries, $elapsedTime);
    }

    private function analyzeQueries($queries)
    {
        $slowQueries = [];
        $duplicateQueries = [];
        $totalTime = 0;
        $queryMap = [];

        foreach ($queries as $query) {
            $totalTime += $query['time'];

            // Detect slow queries (> 100ms)
            if ($query['time'] > 100) {
                $slowQueries[] = $query;
            }

            // Detect duplicate queries
            $key = md5($query['sql']);
            if (isset($queryMap[$key])) {
                $queryMap[$key]++;
            } else {
                $queryMap[$key] = 1;
            }
        }

        printf("  Total Time: %.2fms\n", $totalTime);
        printf("  Slow Queries (>100ms): %d\n", count($slowQueries));

        // Detect N+1 queries
        $groupedQueries = [];
        foreach ($queries as $q) {
            $sql = preg_replace('/\?/', '?', $q['sql']);
            $groupedQueries[$sql][] = $q;
        }

        $n1Detected = false;
        foreach ($groupedQueries as $sql => $group) {
            if (count($group) > 5 && strlen($sql) < 200) {
                echo "  ⚠️  N+1 DETECTED: Query repeated " . count($group) . " times\n";
                echo "      SQL: " . substr($sql, 0, 80) . "...\n";
                $n1Detected = true;
            }
        }

        if (!$n1Detected) {
            echo "  ✅ No obvious N+1 queries detected\n";
        }

        // Check for missing indexes
        foreach ($slowQueries as $query) {
            if (strpos($query['sql'], 'where') !== false && strpos($query['sql'], 'like') !== false) {
                echo "  ⚠️  LIKE query (potentially slow): " . substr($query['sql'], 0, 80) . "...\n";
            }
        }
    }

    private function analyzeCacheUsage()
    {
        // Get Redis info if available
        try {
            $redis = Cache::store('redis')->connection();
            $info = $redis->info();
            $dbSize = $redis->dbsize();

            printf("  Redis DB Size: %d keys\n", $dbSize);
            printf("  Used Memory: %s\n", $info['used_memory_human'] ?? 'N/A');
            printf("  Connected Clients: %d\n", $info['connected_clients'] ?? 0);
            
            // Estimate cache effectiveness
            $keyspaceMisses = $info['keyspace_misses'] ?? 0;
            $keyspaceHits = $info['keyspace_hits'] ?? 0;
            $total = $keyspaceMisses + $keyspaceHits;
            
            if ($total > 0) {
                $hitRatio = ($keyspaceHits / $total) * 100;
                printf("  Cache Hit Ratio: %.1f%%\n", $hitRatio);
                
                if ($hitRatio < 70) {
                    echo "  ⚠️  Cache hit ratio below 70% - consider longer TTL\n";
                }
            }
        } catch (\Exception $e) {
            echo "  ℹ️  Redis not available for analysis\n";
        }
    }

    private function detectIssues($queries, $elapsedTime)
    {
        $issuesFound = 0;

        // Issue 1: Too many queries
        if (count($queries) > 20) {
            echo "  🔴 High query count (" . count($queries) . " queries for $elapsedTime ms)\n";
            $issuesFound++;
        }

        // Issue 2: Slow response
        if ($elapsedTime > 500) {
            echo "  🔴 Slow response time ($elapsedTime ms)\n";
            $issuesFound++;
        }

        // Issue 3: Check for LIKE with wildcards
        foreach ($queries as $query) {
            if (preg_match('/like\s+[\'"]%/', $query['sql'], $matches)) {
                echo "  🟡 Potential index miss: Leading wildcard LIKE query\n";
                echo "     Consider FULLTEXT index\n";
                $issuesFound++;
                break;
            }
        }

        // Issue 4: Memory pressure
        gc_collect_cycles();
        $memUsage = memory_get_usage(true) / 1024 / 1024;
        $memLimit = (int)ini_get('memory_limit');
        $ratio = ($memUsage / $memLimit) * 100;

        if ($ratio > 80) {
            echo "  🟡 High memory usage: $ratio% of limit\n";
            $issuesFound++;
        }

        if ($issuesFound == 0) {
            echo "  ✅ No major issues detected\n";
        }
    }

    public function runDiagnostics()
    {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║             E-JOURNAL SYSTEM DIAGNOSTICS                    ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";

        // Profile each endpoint
        $endpoints = [
            '/api/catalog/authors' => 'Authors Catalog',
            '/api/catalog/tags' => 'Tags Catalog',
            '/api/documents/1' => 'Single Document',
            '/api/documents/1/recommendations' => 'Recommendations',
        ];

        foreach ($endpoints as $url => $name) {
            $this->profileEndpoint($url, $name);
        }

        // Summary
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║                    OPTIMIZATION SUMMARY                     ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        echo "Quick Wins:\n";
        echo "1. ✅ Ensure all indexes from migration are applied: php artisan migrate\n";
        echo "2. ✅ Verify Redis is running: redis-cli ping\n";
        echo "3. ✅ Check FULLTEXT index on abstract: SHOW INDEX FROM documents;\n";
        echo "4. ✅ Monitor query logs: DB::enableQueryLog() + DB::getQueryLog()\n";
        echo "5. ✅ Use Laravel Horizon for queue monitoring (if async)\n";
    }
}

$profiler = new AdvancedProfiler();
$profiler->runDiagnostics();
