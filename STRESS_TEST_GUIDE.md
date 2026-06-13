# Stress Test & Performance Analysis Guide

## 📋 Overview

Scripts ini melakukan testing komprehensif untuk mendeteksi:
- **Bottlenecks** (area lambat)
- **Pattern growth** (linear vs exponential)
- **Cache effectiveness** (hit ratio, miss patterns)
- **Database issues** (N+1 queries, missing indexes)
- **Event analysis** (slow queries, memory leaks)

---

## 🚀 Quick Start

### Prerequisites
```bash
# Pastikan Laravel app sudah disetup
composer install
php artisan migrate

# Pastikan Redis running (optional tapi recommended)
redis-cli ping
# Expected output: PONG

# Pastikan server Laravel jalan
php artisan serve
# Expected: Development Server running at http://127.0.0.1:8000
```

### Jalankan Stress Test

#### Option 1: Profiler Cepat (Recommended untuk pertama kali)
```bash
php profiler.php
```

Output akan menunjukkan:
- Metrics per endpoint
- Query analysis (jumlah, waktu, N+1 detection)
- Cache usage
- Issues & recommendations

#### Option 2: Stress Test dengan Load Progresif
```bash
php stress-test.php
```

Output akan menunjukkan:
- Response time vs concurrency level
- Throughput (RPS)
- Pattern analysis (exponential vs linear)
- Memory usage

---

## 📊 Interpreting Results

### Response Time Pattern Analysis

#### 🟢 LINEAR / STABLE (Ideal)
```
Concurrency: 1    → Response Time: 100ms
Concurrency: 5    → Response Time: 110ms
Concurrency: 10   → Response Time: 120ms
Concurrency: 50   → Response Time: 150ms

Pattern: Ratio ~1.1x → ✅ Good scaling
```

**Apa artinya:** Sistem bisa handle load dengan baik. Setiap kali beban naik 5x, waktu respons hanya naik sedikit.

---

#### 🟡 DEGRADING (Perhatian)
```
Concurrency: 1    → Response Time: 100ms
Concurrency: 5    → Response Time: 250ms
Concurrency: 10   → Response Time: 450ms

Pattern: Ratio ~1.6x → ⚠️ Significant increase
```

**Apa artinya:** Sistem mulai stress. Ada bottleneck tapi masih manageable.

**Penyebab potensial:**
- Database connection pool terbatas
- Cache miss rate tinggi
- Disk I/O saturation
- CPU 100%

---

#### 🔴 EXPONENTIAL (Critical)
```
Concurrency: 1    → Response Time: 50ms
Concurrency: 5    → Response Time: 150ms
Concurrency: 10   → Response Time: 500ms
Concurrency: 50   → Response Time: 3000ms

Pattern: Ratio >1.5x → ❌ Needs immediate optimization
```

**Apa artinya:** Sistem collapse under load. Urgent optimization needed.

**Penyebab potensial:**
- **N+1 queries** (1 query menjadi 100 query karena loop)
- **Missing indexes** (full table scan setiap request)
- **Cache miss** (semua request hit database)
- **Memory exhaustion** (garbage collection overhead)
- **Lock contention** (database locks)

---

## 🔍 Event Analysis

### Jenis Event yang Dianalisis

#### 1. Database Queries
```
Total Queries: 45 queries untuk 1 endpoint
Slow Queries: 3 queries > 100ms

Detected: N+1 DETECTED
- Query repeated 12 times: 
  SELECT * FROM tags WHERE document_id = ?
  
Issue: Setiap iterasi execute query baru (instead of eager load)
Fix: Use WITH or JOIN
```

**Solusi:**
```php
// ❌ BAD - N+1 queries
$documents = Document::all();
foreach ($documents as $doc) {
    echo $doc->tags; // This triggers query untuk setiap doc
}

// ✅ GOOD - Eager loading
$documents = Document::with('tags')->get(); // 2 queries total
foreach ($documents as $doc) {
    echo $doc->tags; // Already loaded
}
```

---

#### 2. Cache Analysis
```
Redis DB Size: 150 keys
Cache Hit Ratio: 65%
Used Memory: 5.2MB

⚠️ Cache hit ratio below 70% - consider longer TTL
```

**Apa artinya:** 35% dari requests tidak hit cache, harus query database.

**Penyebab:**
- TTL terlalu pendek
- Cache key explosion (terlalu banyak kombinasi)
- Cache eviction (memory penuh, hapus key lama)

**Solusi:**
```php
// Increase TTL
Cache::remember('authors', 48 * 60, function () {
    return Author::all(); // Dari 24 jam jadi 48 jam
});

// Atau simplify cache key
// Instead of: cache_doc_1_user_2_sort_date
// Use: cache_doc_1 (simpler, higher hit rate)
```

---

#### 3. Memory & Resource Usage
```
Memory Used: 125MB / 256MB limit (49% of limit)
Slow query: SELECT with leading wildcard (abstract LIKE '%term%')
Potential index miss: No FULLTEXT on abstract column
```

---

## 💡 Common Issues & Solutions

### Issue 1: High Query Count
```
Total Queries: 150 queries
Issue: N+1 in loop
```

**Detection:**
```php
DB::listen(function ($query) {
    if ($query['time'] < 5) {
        echo "Same query repeated?\n";
        echo $query['sql'] . "\n";
    }
});
```

**Fix:**
```php
// Use eager loading
$documents = Document::with(['author', 'tags', 'recommendations'])->get();

// Use select specific columns
$authors = Author::select('id', 'name')->get();

// Use whereExists instead of whereIn
$docs = Document::whereExists(
    fn($q) => $q->from('document_tag')
        ->whereIn('tag_id', [1,2,3])
        ->whereColumn('document_id', 'documents.id')
)->get();
```

---

### Issue 2: Exponential Response Time

**Diagnosis:**
1. Jalankan stress test
2. Lihat apakah response time naik drastis (>2x per 10% load increase)
3. Jalankan profiler untuk deteksi root cause

**Common causes:**
- ❌ Full table scan (missing index)
- ❌ Cache miss on expensive query
- ❌ Connection pool exhausted
- ❌ Garbage collection pressure
- ❌ Disk I/O saturation

**Quick fixes:**
```bash
# 1. Ensure migrations applied
php artisan migrate

# 2. Check indexes exist
# Run in MySQL/MariaDB:
SHOW INDEX FROM documents;
SHOW INDEX FROM subscriptions;

# 3. Enable slow query log
# In MySQL: SET GLOBAL slow_query_log = 'ON';
# Log queries > 500ms

# 4. Flush and warm up cache
php artisan tinker
> Cache::flush()
> Cache::remember('authors', 86400, fn() => Author::all())
```

---

### Issue 3: Low Cache Hit Ratio
```
Cache Hit Ratio: 35%
Issue: Most requests miss cache
```

**Diagnosis:**
```php
// Check Redis
redis-cli
> INFO stats
  keyspace_hits: 1250
  keyspace_misses: 3750
  hit_ratio = 1250/(1250+3750) = 25%
```

**Solutions:**
1. **Increase TTL** (if data doesn't need real-time)
   ```php
   const TTL_CATALOG = 86400 * 7; // 7 days instead of 1 day
   ```

2. **Warm up cache on startup**
   ```php
   // In AppServiceProvider
   public function boot() {
       Cache::remember('all:authors', 86400, fn() => Author::all());
       Cache::remember('all:tags', 86400, fn() => Tag::all());
   }
   ```

3. **Simplify cache keys** (reduce key explosion)
   ```php
   // ❌ Too many combinations
   cache_doc_{id}_user_{user_id}_sort_{sort}
   
   // ✅ Better
   cache_doc_{id}
   ```

---

## 📈 Performance Targets

| Endpoint | Load (req/s) | Mean Response | P95 | P99 |
|----------|---|---|---|---|
| /api/catalog/authors | 100+ | <50ms | <100ms | <200ms |
| /api/catalog/tags | 100+ | <50ms | <100ms | <200ms |
| /api/documents/{id} | 50+ | <100ms | <200ms | <500ms |
| /api/documents/{id}/recommendations | 10+ | <300ms | <800ms | <2000ms |

---

## 🔧 Optimization Checklist

- [ ] **Migrations applied**: `php artisan migrate`
- [ ] **Indexes verified**: `SHOW INDEX FROM table_name;`
- [ ] **Redis running**: `redis-cli ping` → PONG
- [ ] **FULLTEXT index on abstract**: For `LIKE '%term%'` queries
- [ ] **Query log enabled**: `DB::enableQueryLog()`
- [ ] **No N+1 queries**: Use eager loading with `->with()`
- [ ] **Cache TTL appropriate**: Longer TTL = better hit rate
- [ ] **Cache warming**: Pre-load hot data on startup
- [ ] **Database connection pool**: Check `config/database.php`
- [ ] **Memory limit sufficient**: Min 512MB for production

---

## 📝 Running Periodic Tests

### Weekly Performance Baseline
```bash
# Capture baseline
php profiler.php > baseline-week-$(date +%Y%m%d).txt
php stress-test.php > stress-week-$(date +%Y%m%d).json

# Compare with previous week
diff baseline-week-*.txt
```

### Monitor Production
```bash
# Use Laravel Telescope or Horizon
php artisan telescope:install
php artisan telescope:publish

# Or use custom monitoring
php artisan tinker
> DB::listen(fn($q) => $q->time > 500 ? log_slow_query($q) : null)
```

---

## 🎯 Next Steps

1. **Run profiler.php** - Get baseline metrics
2. **Identify bottleneck** - Which endpoint is slowest?
3. **Apply optimization** - Use solutions above
4. **Re-test** - Compare before/after
5. **Monitor** - Set up continuous monitoring
