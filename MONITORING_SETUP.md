# Production Monitoring Setup Guide

## 📊 Overview

Sistem monitoring untuk E-Journal Digital Library telah di-setup untuk production readiness. Tools ini membantu detect degradation, bottlenecks, dan issues sebelum impact end-users.

---

## 🚀 Quick Start

### Start Monitoring (Real-time Dashboard)
```bash
# Terminal 1: Start monitoring with 5-minute intervals
php artisan monitor:performance --interval=5

# Or with file output (for log aggregation)
php artisan monitor:performance --interval=5 --output=file
```

**Output:**
```
╔════════════════════════════════════════════════════════════╗
║ PERFORMANCE METRICS - 2026-06-11 14:32:45
╚════════════════════════════════════════════════════════════╝

📊 REDIS CACHE
─────────────────────────────────────────────────────────────
  Status: ✅ Online
  Keys: 156
  Memory: 5.2MB
  Clients: 3
  Hit Ratio: 89.3% 🟢 Excellent

💾 DATABASE
─────────────────────────────────────────────────────────────
  Status: ✅ Online
  Active Connections: 5/100
  Usage: 5% 🟢 Healthy

⚙️  SYSTEM RESOURCES
─────────────────────────────────────────────────────────────
  Memory: 256MB / 512MB
  Status: 🟢 Healthy (50%)
```

---

## 🎯 What to Monitor

### 1. Redis Cache Health

**Key Metrics:**
- **Hit Ratio:** Target ≥ 80%
  ```bash
  redis-cli INFO stats
  # Look for: keyspace_hits / (keyspace_hits + keyspace_misses)
  
  # If < 80%:
  # - Increase TTL for catalog endpoints
  # - Check if cache is being evicted
  # - Run: Cache::flush() to reset
  ```

- **Memory Usage:** Target < 80% of Redis max
  ```bash
  redis-cli INFO memory
  # Look for: used_memory_human
  
  # If > 80%:
  # - Identify large keys: redis-cli --bigkeys
  # - Reduce TTL for less important data
  # - Scale up Redis instance
  ```

- **Client Connections:** Target < 50
  ```bash
  redis-cli INFO clients
  # Look for: connected_clients
  
  # If > 50:
  # - Check for connection leaks
  # - Review connection pooling config
  ```

---

### 2. Database Connection Health

**Key Metrics:**
- **Active Connections:** Target < 70% of max
  ```bash
  # In MySQL:
  SHOW PROCESSLIST;
  SHOW STATUS LIKE 'Threads_connected';
  
  # If approaching limit:
  # - Check for long-running queries: SHOW FULL PROCESSLIST;
  # - Increase pool size in config/database.php
  # - Consider connection pooling service (ProxySQL, MaxScale)
  ```

- **Slow Query Log:** Target 0-1 per hour
  ```bash
  # Enable:
  SET GLOBAL slow_query_log = 'ON';
  SET GLOBAL long_query_time = 0.1; # 100ms threshold
  
  # Check:
  TAIL /var/log/mysql/slow.log
  
  # If many slow queries:
  # - Run EXPLAIN on slow queries
  # - Add missing indexes
  # - Optimize query logic
  ```

---

### 3. System Resources

**Key Metrics:**
- **Memory:** Target < 80% of limit
  ```bash
  # Check Laravel memory:
  php artisan tinker
  > echo memory_get_usage(true) / 1024 / 1024;
  
  # If > 80%:
  # - Check for memory leaks in loops
  # - Enable query caching
  # - Increase PHP memory_limit in php.ini
  ```

- **CPU:** Target < 70% average
  ```bash
  # Monitor:
  top
  ps aux | grep php
  
  # If > 70%:
  # - Profile slow endpoints
  # - Enable caching
  # - Consider horizontal scaling
  ```

---

## 📈 Monitoring Dashboard Setup (Optional)

### Option 1: Simple File-based Monitoring
```bash
# Collect metrics every 5 minutes
php artisan monitor:performance --interval=5 --output=file

# View metrics:
tail -f storage/logs/performance-metrics.log

# Analyze trends:
cat storage/logs/performance-metrics.log | \
  jq '.redis.hit_ratio' | \
  awk '{sum+=$1} END {print "Avg hit ratio: " sum/NR "%"}'
```

---

### Option 2: Laravel Telescope (Built-in)
```bash
# Install (if not already)
php artisan telescope:install
php artisan telescope:publish

# Access: http://localhost:8000/telescope
# Provides: Request timeline, Queries, Cache hits, Logs, Events
```

---

### Option 3: Advanced - Prometheus + Grafana
```bash
# Install Prometheus metrics exporter
composer require promphp/prometheus_client

# Setup in routes/api.php:
Route::get('/metrics', function() {
    return \Prometheus\CollectorRegistry::getDefault()->render();
});

# Then setup Grafana dashboard to scrape /metrics
```

---

## ⚠️ Alert Thresholds

### Critical (Immediate Action Required)
| Metric | Threshold | Action |
|--------|-----------|--------|
| Cache Hit Ratio | < 50% | Flush & warm cache, check eviction |
| DB Connections | > 90% | Increase pool, check for leaks |
| Memory Usage | > 90% | Check for leaks, increase limit |
| Redis Offline | - | Restart Redis, check logs |
| DB Offline | - | Check database, review logs |

### Warning (Monitor Closely)
| Metric | Threshold | Action |
|--------|-----------|--------|
| Cache Hit Ratio | 50-80% | Increase TTL, reduce key variety |
| DB Connections | 70-90% | Plan scaling, monitor growth |
| Memory Usage | 80-90% | Review allocation, optimize code |
| Redis Memory | 80-100% | Monitor growth, plan expansion |

### Healthy (No Action)
| Metric | Threshold |
|--------|-----------|
| Cache Hit Ratio | > 80% |
| DB Connections | < 70% |
| Memory Usage | < 80% |
| Response Time | < 50ms avg |

---

## 🔄 Weekly Maintenance

### Monday Morning Checklist
```bash
# 1. Review past week's metrics
tail -n 2000 storage/logs/performance-metrics.log | jq '.' > weekly-report.json

# 2. Check for trends (increasing response time, decreasing cache hit)
cat weekly-report.json | jq '.redis.hit_ratio'
cat weekly-report.json | jq '.database.connection_usage'

# 3. Review slow queries from past week
grep "# Time: " /var/log/mysql/slow.log | tail -100

# 4. Check error logs
tail -100 storage/logs/laravel.log | grep ERROR

# 5. Run quick stress test to verify no regression
php artisan test:stress --concurrency=50 --requests=100
```

### Monthly Performance Review
```bash
# Run full stress test
php artisan test:stress --concurrency=100 --requests=500

# Compare with baseline (from STRESS_TEST_REPORT.md):
# If any endpoint > 10ms degradation: investigate

# Update baseline if optimization applied:
# cp STRESS_TEST_REPORT.md STRESS_TEST_REPORT_$(date +%Y%m%d).md
```

---

## 🚨 Common Issues & Fixes

### Issue 1: Cache Hit Ratio Drops Below 70%

**Symptoms:**
- Redis shows `keyspace_hits: 100`, `keyspace_misses: 500`
- Hit ratio: 17% (below target 80%)

**Root Causes:**
1. **Cache eviction** (memory full)
   ```bash
   redis-cli CONFIG GET maxmemory-policy
   # If "allkeys-lru", keys being removed when memory full
   ```

2. **TTL too short**
   ```php
   // In CacheService.php:
   const TTL_CATALOG = 86400;      // 24h
   const TTL_DOCUMENT = 3600;      // 1h
   const TTL_RECOMMENDATIONS = 1800; // 30min
   
   // Consider increasing:
   const TTL_CATALOG = 86400 * 7;  // 7 days
   ```

3. **Cache key explosion** (too many unique keys)
   ```bash
   redis-cli KEYS '*' | wc -l
   # If > 10,000: too many keys, simplify cache strategy
   ```

**Fix:**
```bash
# 1. Increase Redis memory
# 2. Increase TTL for long-lived data
# 3. Warm cache on app startup
# 4. Simplify cache keys (combine filters)
```

---

### Issue 2: DB Connections > 80%

**Symptoms:**
- Active connections: 90/100
- New queries timeout waiting for connection

**Root Causes:**
1. **Slow queries holding connections**
   ```bash
   SHOW FULL PROCESSLIST;
   # Look for long-running queries (Time > 60s)
   ```

2. **Connection leak** (not releasing)
   ```php
   DB::listen(function($query) {
       if ($query->time > 1000) {
           logger()->warning("Slow query: " . $query->sql);
       }
   });
   ```

**Fix:**
```bash
# 1. Increase pool size in config/database.php:
'connections' => [
    'mysql' => [
        'pool' => [
            'max' => 30,  // Increase from default
        ],
    ],
],

# 2. Kill long-running queries:
KILL QUERY <process_id>;

# 3. Add indexes for slow queries:
EXPLAIN <slow_query>;
# Check if Using index, if not: add index
```

---

### Issue 3: Memory Usage > 85%

**Symptoms:**
- Laravel process memory > 400MB
- Queries becoming slow
- Occasional OOM errors

**Root Causes:**
1. **Memory leak in loop**
   ```php
   // ❌ BAD
   $docs = Document::all();
   foreach ($docs as $doc) {
       // Each iteration accumulates memory
   }
   
   // ✅ GOOD
   Document::chunk(500, function($docs) {
       foreach ($docs as $doc) {
           // Memory reset each chunk
       }
   });
   ```

2. **Caching too much data**
   ```php
   // ❌ BAD - caching all users
   Cache::remember('all_users', 86400, fn() => User::all());
   
   // ✅ GOOD - cache only active
   Cache::remember('active_users', 3600, 
       fn() => User::where('active', true)->limit(1000)->get()
   );
   ```

**Fix:**
```bash
# 1. Use chunking for large datasets
# 2. Limit cached results
# 3. Increase PHP memory_limit in php.ini
# 4. Profile code with Xdebug
```

---

## 📞 Support Reference

**When to escalate:**
- Cache hit ratio < 50% for 2+ hours
- DB connections > 90% for 1+ hour
- Memory > 90% for 30+ minutes
- Response time > 200ms for 5+ minutes

**What to collect when reporting:**
```bash
# 1. Current metrics
php artisan monitor:performance --interval=1 # Run for 5 min

# 2. Last 100 slow queries
tail -100 /var/log/mysql/slow.log

# 3. Last 50 Laravel errors
tail -50 storage/logs/laravel.log | grep ERROR

# 4. System info
uname -a
free -h
df -h

# 5. Recent stress test results
php artisan test:stress --concurrency=50 --requests=100
```

---

## ✅ Setup Complete

Your monitoring setup is complete. Monitor regularly and maintain performance! 🚀
