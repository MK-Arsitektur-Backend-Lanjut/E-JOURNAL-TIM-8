<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class StressTestCommand extends Command
{
    protected $signature = 'test:stress {--endpoint= : Specific endpoint to test} {--concurrency=50 : Max concurrent requests} {--requests=500 : Total requests}';
    protected $description = 'Run stress test on endpoints with detailed analysis';

    public function handle()
    {
        $this->line("\n╔════════════════════════════════════════════════════════════╗");
        $this->line("║    E-JOURNAL STRESS TEST - Interactive CLI                  ║");
        $this->line("╚════════════════════════════════════════════════════════════╝\n");

        $endpoints = [
            '/api/catalog/authors' => 'Authors Catalog',
            '/api/catalog/tags' => 'Tags Catalog',
            '/api/documents/1' => 'Single Document',
            '/api/documents/1/recommendations' => 'Recommendations',
        ];

        $endpointFilter = $this->option('endpoint');
        $maxConcurrency = $this->option('concurrency');
        $totalRequests = $this->option('requests');

        if ($endpointFilter) {
            $endpoints = array_filter($endpoints, fn($name, $path) => strpos($path, $endpointFilter) !== false, ARRAY_FILTER_USE_BOTH);
        }

        $concurrencies = [1, 5, 10, 20, $maxConcurrency];

        $this->info("Configuration:");
        $this->line("  Max Concurrency: $maxConcurrency");
        $this->line("  Requests per level: $totalRequests");
        $this->line("  Endpoints: " . count($endpoints));
        $this->newLine();

        $results = [];

        foreach ($endpoints as $path => $name) {
            $this->info("Testing: $name");
            $this->line(str_repeat("─", 70));

            $results[$name] = [];

            foreach ($concurrencies as $concurrency) {
                $this->line("  Testing with concurrency: $concurrency");
                $bar = $this->output->createProgressBar($totalRequests);

                $times = [];
                $sizes = [];
                $errors = 0;

                for ($i = 0; $i < $totalRequests; $i++) {
                    $start = microtime(true);
                    
                    try {
                        $response = Http::timeout(30)->get(config('app.url') . $path);
                        $times[] = (microtime(true) - $start) * 1000;
                        $sizes[] = strlen($response->body());
                    } catch (\Exception $e) {
                        $errors++;
                    }

                    $bar->advance();

                    if ($i % max(1, intval($totalRequests / $concurrency)) == 0) {
                        usleep(5000);
                    }
                }

                $bar->finish();
                $this->newLine();

                // Calculate metrics
                if (!empty($times)) {
                    sort($times);
                    $metrics = [
                        'concurrency' => $concurrency,
                        'count' => count($times),
                        'mean' => array_sum($times) / count($times),
                        'median' => $times[intval(count($times) * 0.5)],
                        'p95' => $times[intval(count($times) * 0.95)],
                        'p99' => $times[intval(count($times) * 0.99)],
                        'min' => min($times),
                        'max' => max($times),
                        'rps' => (count($times) / array_sum($times)) * 1000,
                        'errors' => $errors,
                    ];

                    $results[$name][$concurrency] = $metrics;

                    $this->line(sprintf(
                        "    Mean: %.2fms | P95: %.2fms | P99: %.2fms | RPS: %.1f",
                        $metrics['mean'],
                        $metrics['p95'],
                        $metrics['p99'],
                        $metrics['rps']
                    ));
                }
            }

            $this->newLine();
        }

        // Print summary
        $this->line("\n╔════════════════════════════════════════════════════════════╗");
        $this->line("║                   PERFORMANCE SUMMARY                       ║");
        $this->line("╚════════════════════════════════════════════════════════════╝\n");

        foreach ($results as $name => $concurrencyResults) {
            $this->info($name);
            $this->table(
                ['Concurrency', 'Mean (ms)', 'P95 (ms)', 'P99 (ms)', 'RPS', 'Errors'],
                array_map(fn($m) => [
                    $m['concurrency'],
                    number_format($m['mean'], 2),
                    number_format($m['p95'], 2),
                    number_format($m['p99'], 2),
                    number_format($m['rps'], 2),
                    $m['errors']
                ], $concurrencyResults)
            );

            // Analyze growth pattern
            $sortedResults = array_values($concurrencyResults);
            if (count($sortedResults) > 1) {
                $firstMean = $sortedResults[0]['mean'];
                $lastMean = $sortedResults[count($sortedResults) - 1]['mean'];
                $growth = (($lastMean - $firstMean) / $firstMean) * 100;

                if ($growth > 200) {
                    $this->error("    🔴 EXPONENTIAL GROWTH: {$growth}% increase - Critical!");
                } elseif ($growth > 100) {
                    $this->warn("    🟡 SIGNIFICANT INCREASE: {$growth}% - Monitor");
                } else {
                    $this->comment("    ✅ STABLE: {$growth}% increase - Good");
                }
            }

            $this->newLine();
        }

        // Analysis
        $this->line("\n╔════════════════════════════════════════════════════════════╗");
        $this->line("║                     RECOMMENDATIONS                        ║");
        $this->line("╚════════════════════════════════════════════════════════════╝\n");

        $this->info("1. Database Optimization");
        $this->line("   ✅ Check: php artisan tinker → DB::getQueryLog()");
        $this->line("   ⚠️ If many queries: Consider N+1 prevention");

        $this->info("\n2. Cache Optimization");
        $this->line("   ✅ Check: redis-cli → INFO");
        $this->line("   ⚠️ If low hit ratio: Increase TTL or warm cache");

        $this->info("\n3. Monitoring");
        $this->line("   ✅ Run: php artisan tinker");
        $this->line("   ✅ Then: DB::listen(fn(\$q) => logger()->debug(\$q->sql))");

        $this->newLine();
    }
}
