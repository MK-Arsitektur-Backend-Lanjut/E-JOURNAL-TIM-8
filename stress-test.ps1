# Stress Test Script untuk E-Journal Digital Library
# Mengukur performance dengan load progresif

$baseUrl = "http://localhost:8000"
$endpoints = @(
    "/api/catalog/authors",
    "/api/catalog/tags",
    "/api/documents/1",
    "/api/documents/1/recommendations"
)

$results = @()
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$logFile = "stress-test-results-$timestamp.json"

Write-Host "=== E-JOURNAL STRESS TEST ===" -ForegroundColor Cyan
Write-Host "Base URL: $baseUrl" -ForegroundColor Cyan
Write-Host "Timestamp: $timestamp" -ForegroundColor Cyan
Write-Host ""

# Konfigurasi concurrency levels
$concurrencies = @(1, 5, 10, 20, 50, 100)
$requestsPerLevel = 500

foreach ($endpoint in $endpoints) {
    Write-Host "Testing Endpoint: $endpoint" -ForegroundColor Yellow
    Write-Host "=" * 60
    
    foreach ($concurrency in $concurrencies) {
        $url = "$baseUrl$endpoint"
        $command = "ab -n $requestsPerLevel -c $concurrency -g 'ab-results-$concurrency.tsv' -H 'Accept: application/json' '$url'"
        
        Write-Host "  [Concurrent: $concurrency] Running $requestsPerLevel requests..." -ForegroundColor Green
        
        # Jalankan Apache Bench
        $output = & cmd /c $command 2>&1
        
        # Parse hasil
        $lines = $output -split "`n"
        
        $metrics = @{
            endpoint = $endpoint
            concurrency = $concurrency
            totalRequests = $requestsPerLevel
            timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
            results = @()
        }
        
        # Extract key metrics dari output
        foreach ($line in $lines) {
            if ($line -match "Time taken for tests:\s*([\d.]+)\s*sec") {
                $metrics['totalTime'] = [double]$matches[1]
            }
            if ($line -match "Requests per second:\s*([\d.]+)\s*\[#/sec\]") {
                $metrics['rps'] = [double]$matches[1]
            }
            if ($line -match "Time per request:\s*([\d.]+)\s*\[ms\].*\(mean\)") {
                $metrics['meanResponseTime'] = [double]$matches[1]
            }
            if ($line -match "Time per request:\s*([\d.]+)\s*\[ms\].*\(mean, across all concurrent requests\)") {
                $metrics['concurrentMean'] = [double]$matches[1]
            }
            if ($line -match "Percentage of requests served within a certain time \(ms\)") {
                $captureNext = $true
            }
            if ($captureNext -and $line -match "50%\s*([\d.]+)") {
                $metrics['p50'] = [double]$matches[1]
                $captureNext = $false
            }
            if ($line -match "50%\s*([\d.]+)") {
                $metrics['p50'] = [double]$matches[1]
            }
            if ($line -match "90%\s*([\d.]+)") {
                $metrics['p90'] = [double]$matches[1]
            }
            if ($line -match "95%\s*([\d.]+)") {
                $metrics['p95'] = [double]$matches[1]
            }
            if ($line -match "99%\s*([\d.]+)") {
                $metrics['p99'] = [double]$matches[1]
            }
            if ($line -match "100%\s*([\d.]+)") {
                $metrics['p100'] = [double]$matches[1]
            }
        }
        
        $results += $metrics
        
        # Display hasil
        if ($metrics.ContainsKey('rps')) {
            Write-Host "    RPS: $($metrics['rps']) req/s | Mean: $($metrics['meanResponseTime'])ms | P95: $($metrics['p95'])ms" -ForegroundColor Cyan
        }
        
        Start-Sleep -Seconds 2  # Cool down antar test
    }
    
    Write-Host ""
}

# Simpan hasil ke JSON
$results | ConvertTo-Json -Depth 10 | Out-File -FilePath $logFile
Write-Host "Results saved to: $logFile" -ForegroundColor Green

# Generate summary report
Write-Host "`n=== STRESS TEST SUMMARY ===" -ForegroundColor Cyan

foreach ($endpoint in $endpoints) {
    $endpointResults = $results | Where-Object { $_.endpoint -eq $endpoint }
    
    Write-Host "`nEndpoint: $endpoint" -ForegroundColor Yellow
    Write-Host "Concurrency | RPS    | Mean RT (ms) | P95 (ms) | P99 (ms)" -ForegroundColor White
    Write-Host "-" * 60
    
    foreach ($result in $endpointResults | Sort-Object concurrency) {
        if ($result.ContainsKey('rps')) {
            Write-Host ("{0,-11} | {1,-6:F1} | {2,-12:F2} | {3,-8:F2} | {4,-8:F2}" -f `
                $result.concurrency, `
                $result.rps, `
                $result.meanResponseTime, `
                $result.p95, `
                $result.p99) -ForegroundColor Cyan
        }
    }
}

Write-Host "`n=== ANALYSIS ===" -ForegroundColor Magenta

foreach ($endpoint in $endpoints) {
    $endpointResults = $results | Where-Object { $_.endpoint -eq $endpoint } | Sort-Object concurrency
    
    if ($endpointResults.Count -gt 1) {
        $firstResult = $endpointResults[0]
        $lastResult = $endpointResults[-1]
        
        $timeIncrease = (($lastResult.meanResponseTime - $firstResult.meanResponseTime) / $firstResult.meanResponseTime) * 100
        
        Write-Host "`n$endpoint" -ForegroundColor Yellow
        Write-Host "Response time increase: $($timeIncrease)%" -ForegroundColor White
        
        if ($timeIncrease -gt 200) {
            Write-Host "⚠️  EXPONENTIAL GROWTH DETECTED - Needs optimization" -ForegroundColor Red
        }
        elseif ($timeIncrease -gt 100) {
            Write-Host "⚠️  SIGNIFICANT INCREASE - Monitor closely" -ForegroundColor Yellow
        }
        else {
            Write-Host "✅ LINEAR OR STABLE PERFORMANCE" -ForegroundColor Green
        }
    }
}
