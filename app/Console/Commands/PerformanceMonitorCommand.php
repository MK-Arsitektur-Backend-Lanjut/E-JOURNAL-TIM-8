<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceMonitorCommand extends Command
{
    protected $signature = 'monitor:performance {--interval=5 : Check interval in minutes} {--output=console : Output format (console/file/webhook)}';
    protected $description = 'Monitor system performance metrics (response time, cache, database)';

    public function handle()
    {
        $interval = $this->option('interval');
        $output = $this->option('output');

        $this->info("🚀 Performance Monitor Starting");
        $this->line("Interval: {$interval} minutes");
        $this->line("Output: {$output}\n");

        while (true) {
            $this->collectMetrics($output);
            sleep($interval * 60);
        }
    }

    private function collectMetrics($output)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $metrics = [
            'timestamp' => $timestamp,
            'redis' => $this->getRedisMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'system' => $this->getSystemMetrics(),
        ];

        $this->displayMetrics($metrics, $output);
        $this->checkAlerts($metrics);
    }

    private function getRedisMetrics()
    {
        try {
            $redis = Cache::store('redis')->connection();
            $info = $redis->info();

            return [
                'status' => 'online',
                'keys' => $redis->dbsize(),
                'memory_mb' => round(($info['used_memory'] ?? 0) / 1024 / 1024, 2),
                'clients' => $info['connected_clients'] ?? 0,
                'commands' => $info['total_commands_processed'] ?? 0,
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_ratio' => $this->calculateHitRatio($info),
            ];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }
    }

    private function calculateHitRatio($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    private function getDatabaseMetrics()
    {
        try {
            $stats = DB::select('SELECT COUNT(*) as count FROM information_schema.processlist WHERE command != "Sleep"');
            $activeConnections = $stats[0]->count ?? 0;

            return [
                'status' => 'online',
                'active_connections' => $activeConnections,
                'max_connections' => (int)DB::selectOne('SELECT @@max_connections as max_conn')->max_conn ?? 0,
                'connection_usage' => round(($activeConnections / (int)DB::selectOne('SELECT @@max_connections as max_conn')->max_conn) * 100, 2),
            ];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }
    }

    private function getSystemMetrics()
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_limit_mb' => round((int)ini_get('memory_limit') / 1024, 2),
            'cpu_load' => shell_exec('wmic os get loadpercentage') ? 'N/A' : 0,
        ];
    }

    private function displayMetrics($metrics, $output)
    {
        $ts = $metrics['timestamp'];
        $redis = $metrics['redis'];
        $db = $metrics['database'];
        $sys = $metrics['system'];

        if ($output === 'console') {
            $this->printConsoleMetrics($ts, $redis, $db, $sys);
        } elseif ($output === 'file') {
            $this->logMetricsToFile($metrics);
        }
    }

    private function printConsoleMetrics($ts, $redis, $db, $sys)
    {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║ PERFORMANCE METRICS - $ts\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        // Redis
        echo "📊 REDIS CACHE\n";
        echo "─────────────────────────────────────────────────────────────\n";
        if ($redis['status'] === 'online') {
            echo "  Status: ✅ Online\n";
            echo "  Keys: {$redis['keys']}\n";
            echo "  Memory: {$redis['memory_mb']}MB\n";
            echo "  Clients: {$redis['clients']}\n";
            echo "  Hit Ratio: {$redis['hit_ratio']}% ";
            
            if ($redis['hit_ratio'] >= 80) {
                echo "🟢 Excellent\n";
            } elseif ($redis['hit_ratio'] >= 70) {
                echo "🟡 Good\n";
            } else {
                echo "🔴 Low\n";
            }
        } else {
            echo "  Status: 🔴 OFFLINE\n";
            echo "  Error: {$redis['error']}\n";
        }

        // Database
        echo "\n💾 DATABASE\n";
        echo "─────────────────────────────────────────────────────────────\n";
        if ($db['status'] === 'online') {
            echo "  Status: ✅ Online\n";
            echo "  Active Connections: {$db['active_connections']}/{$db['max_connections']}\n";
            echo "  Usage: {$db['connection_usage']}% ";
            
            if ($db['connection_usage'] < 70) {
                echo "🟢 Healthy\n";
            } elseif ($db['connection_usage'] < 90) {
                echo "🟡 Monitor\n";
            } else {
                echo "🔴 Critical\n";
            }
        } else {
            echo "  Status: 🔴 OFFLINE\n";
        }

        // System
        echo "\n⚙️  SYSTEM RESOURCES\n";
        echo "─────────────────────────────────────────────────────────────\n";
        echo "  Memory: {$sys['memory_usage_mb']}MB / {$sys['memory_limit_mb']}MB\n";
        
        $memPercentage = round(($sys['memory_usage_mb'] / $sys['memory_limit_mb']) * 100, 2);
        if ($memPercentage < 70) {
            echo "  Status: 🟢 Healthy ({$memPercentage}%)\n";
        } elseif ($memPercentage < 90) {
            echo "  Status: 🟡 Monitor ({$memPercentage}%)\n";
        } else {
            echo "  Status: 🔴 Critical ({$memPercentage}%)\n";
        }
    }

    private function logMetricsToFile($metrics)
    {
        $logFile = storage_path('logs/performance-metrics.log');
        $line = json_encode($metrics) . "\n";
        file_put_contents($logFile, $line, FILE_APPEND);
    }

    private function checkAlerts($metrics)
    {
        $redis = $metrics['redis'];
        $db = $metrics['database'];
        $sys = $metrics['system'];

        $alerts = [];

        // Redis alerts
        if ($redis['status'] === 'offline') {
            $alerts[] = ['level' => 'critical', 'message' => 'Redis is OFFLINE'];
        }
        if ($redis['hit_ratio'] < 70 && $redis['status'] === 'online') {
            $alerts[] = ['level' => 'warning', 'message' => "Cache hit ratio low: {$redis['hit_ratio']}%"];
        }
        if ($redis['memory_mb'] > 500) {
            $alerts[] = ['level' => 'warning', 'message' => "Redis memory high: {$redis['memory_mb']}MB"];
        }

        // Database alerts
        if ($db['status'] === 'offline') {
            $alerts[] = ['level' => 'critical', 'message' => 'Database is OFFLINE'];
        }
        if ($db['connection_usage'] > 90) {
            $alerts[] = ['level' => 'critical', 'message' => "Connection pool nearly full: {$db['connection_usage']}%"];
        }

        // System alerts
        $memPercentage = round(($sys['memory_usage_mb'] / $sys['memory_limit_mb']) * 100, 2);
        if ($memPercentage > 90) {
            $alerts[] = ['level' => 'critical', 'message' => "Memory critical: {$memPercentage}%"];
        }

        // Display alerts
        foreach ($alerts as $alert) {
            if ($alert['level'] === 'critical') {
                $this->error("🔴 ALERT: {$alert['message']}");
            } else {
                $this->warn("🟡 WARNING: {$alert['message']}");
            }
        }
    }
}
