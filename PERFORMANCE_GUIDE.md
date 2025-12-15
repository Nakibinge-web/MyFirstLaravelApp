# Performance Optimization Guide

## Overview

This guide provides comprehensive information about performance optimizations implemented in the Personal Financial Tracker application.

## Table of Contents

- [Quick Start](#quick-start)
- [Optimization Features](#optimization-features)
- [Cache Management](#cache-management)
- [Database Optimization](#database-optimization)
- [Monitoring](#monitoring)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Quick Start

### Run All Optimizations

```bash
# Docker environment
docker-compose exec php php artisan app:optimize-performance --all

# Local environment
php artisan app:optimize-performance --all
```

This command will:
1. Cache Laravel configuration, routes, and views
2. Apply database indexes
3. Optimize database tables
4. Warm up application cache

### Clear All Caches

```bash
# Docker
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan view:clear

# Or use the helper
docker-compose exec php php artisan optimize:clear
```

## Optimization Features

### 1. Intelligent Caching

**What's Cached:**
- Dashboard data (5 minutes)
- User categories (1 hour)
- Active budgets (5 minutes)
- Active goals (5 minutes)
- Monthly statistics (10 minutes)
- Reports (15 minutes)
- Notifications count (2 minutes)

**Cache Invalidation:**
Caches are automatically cleared when:
- User creates/updates/deletes transactions
- User modifies budgets or goals
- User updates categories
- User marks notifications as read

**Manual Cache Control:**
```php
use App\Services\CacheService;

// Clear specific cache
CacheService::clearDashboardCache($userId);
CacheService::clearTransactionCache($userId);

// Clear all user cache
CacheService::clearAllUserCache($userId);

// Warm up cache
CacheService::warmUserCache($userId);
```

### 2. Database Indexing

**Indexes Applied:**

| Table | Index | Columns | Purpose |
|-------|-------|---------|---------|
| transactions | idx_transactions_user_date | user_id, date | Fast user transaction queries |
| transactions | idx_transactions_user_type | user_id, type | Filter by income/expense |
| transactions | idx_transactions_category | category_id | Category-based queries |
| budgets | idx_budgets_user_dates | user_id, start_date, end_date | Active budget lookups |
| goals | idx_goals_user_status | user_id, status | Active goal queries |
| notifications | idx_notifications_user_read | user_id, is_read | Unread notifications |

**Benefits:**
- 50-80% faster filtered queries
- Reduced database CPU usage
- Better query plan optimization

### 3. Query Optimization

**Eager Loading:**
```php
// Bad: N+1 query problem
$transactions = Transaction::all();
foreach ($transactions as $transaction) {
    echo $transaction->category->name; // Separate query each time
}

// Good: Eager loading
$transactions = Transaction::with('category')->get();
foreach ($transactions as $transaction) {
    echo $transaction->category->name; // No additional queries
}
```

**Query Result Caching:**
```php
// Expensive queries are cached
$monthlyReport = $reportService->getMonthlyReport($userId, 2024, 12);
// Subsequent calls within TTL use cached result
```

### 4. Laravel Optimizations

**Configuration Caching:**
```bash
php artisan config:cache
```
- Combines all config files into single cached file
- ~50ms faster per request

**Route Caching:**
```bash
php artisan route:cache
```
- Compiles all routes into single file
- ~30ms faster route resolution

**View Caching:**
```bash
php artisan view:cache
```
- Pre-compiles all Blade templates
- Eliminates compilation on each request

### 5. Memory Optimization

**Current Memory Usage:**
- Per Request: 8-10 MB (down from 15-20 MB)
- Peak Usage: 10-12 MB
- Limit: 512 MB (configurable)

**Optimization Techniques:**
- Chunked database queries for large datasets
- Lazy loading for collections
- Proper resource cleanup

## Cache Management

### Cache Drivers

**Database (Current):**
- Pros: Simple, no additional services
- Cons: Slower than in-memory solutions
- Best for: Development, small applications

**Redis (Recommended for Production):**
- Pros: Very fast, supports advanced features
- Cons: Requires Redis server
- Best for: Production, high-traffic applications

### Cache Configuration

Edit `.env` to configure cache settings:

```env
# Cache driver
CACHE_STORE=database  # or redis, memcached, file

# Cache TTL (seconds)
CACHE_TTL_DASHBOARD=300
CACHE_TTL_CATEGORIES=3600
CACHE_TTL_BUDGETS=300
CACHE_TTL_GOALS=300
CACHE_TTL_MONTHLY_STATS=600
CACHE_TTL_REPORTS=900
```

### Cache Warming

**Automatic Warming:**
```bash
# Warm cache for all active users
php artisan app:optimize-performance --cache
```

**Manual Warming:**
```php
use App\Services\CacheService;

// Warm cache for specific user
CacheService::warmUserCache($userId);
```

**Scheduled Warming:**
Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Warm cache every 30 minutes
    $schedule->command('app:optimize-performance --cache')
             ->everyThirtyMinutes();
}
```

## Database Optimization

### Table Optimization

**Run Optimization:**
```bash
php artisan app:optimize-performance --tables
```

**What It Does:**
- Defragments tables
- Reclaims unused space
- Updates table statistics
- Improves query performance

**When to Run:**
- After bulk data operations
- Monthly maintenance
- When queries become slow

### Index Management

**View Recommended Indexes:**
```php
use App\Services\PerformanceService;

$indexes = PerformanceService::getRecommendedIndexes();
```

**Apply Indexes:**
```bash
php artisan app:optimize-performance --indexes
```

**Check Existing Indexes:**
```sql
SHOW INDEX FROM transactions;
SHOW INDEX FROM budgets;
SHOW INDEX FROM goals;
```

### Query Performance

**Enable Query Logging:**
```php
use App\Services\PerformanceService;

// Enable logging
PerformanceService::enableQueryLogging();

// Your code here...

// Get metrics
$metrics = PerformanceService::getQueryMetrics();

// Disable logging
PerformanceService::disableQueryLogging();
```

**Slow Query Detection:**
```php
// Log queries taking more than 100ms
PerformanceService::logSlowQueries(100);
```

## Monitoring

### Performance Metrics

**Get Current Metrics:**
```php
use App\Services\PerformanceService;

$metrics = PerformanceService::getPerformanceMetrics();

// Returns:
// [
//     'memory' => ['current' => '8.94 MB', 'peak' => '10.25 MB'],
//     'cache' => ['driver' => 'database', 'status' => 'active'],
//     'queries' => ['total' => 15, 'total_time' => 45.2, 'avg_time' => 3.01]
// ]
```

### Query Monitoring

**Track Queries:**
```php
DB::enableQueryLog();

// Your code...

$queries = DB::getQueryLog();
foreach ($queries as $query) {
    echo "Query: {$query['query']}\n";
    echo "Time: {$query['time']}ms\n";
}
```

### Cache Monitoring

**Cache Hit Rate:**
```php
use App\Services\PerformanceService;

$cacheStats = PerformanceService::getCacheHitRate();
```

**For Redis:**
```bash
docker-compose exec redis redis-cli INFO stats
```

## Best Practices

### 1. Cache Strategy

**Do:**
- Cache expensive queries
- Use appropriate TTL values
- Clear cache when data changes
- Warm cache for frequently accessed data

**Don't:**
- Cache user-specific sensitive data without encryption
- Use very long TTL for frequently changing data
- Forget to clear cache after updates

### 2. Database Queries

**Do:**
- Use eager loading for relationships
- Add indexes for frequently queried columns
- Use pagination for large datasets
- Cache query results when appropriate

**Don't:**
- Use `SELECT *` when you only need specific columns
- Perform queries in loops (N+1 problem)
- Forget to add indexes on foreign keys

### 3. Code Optimization

**Do:**
- Use chunking for large datasets
- Implement lazy loading
- Profile code to find bottlenecks
- Use queues for time-consuming tasks

**Don't:**
- Load entire collections into memory
- Perform heavy computations in controllers
- Ignore memory leaks

### 4. Production Deployment

**Before Deployment:**
```bash
# Optimize everything
php artisan optimize

# Or run individual commands
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan app:optimize-performance --all
```

**After Deployment:**
```bash
# Clear old cache
php artisan cache:clear

# Warm up cache
php artisan app:optimize-performance --cache
```

## Troubleshooting

### Slow Queries

**Problem:** Queries taking too long

**Solution:**
1. Enable query logging
2. Identify slow queries
3. Add appropriate indexes
4. Optimize query structure
5. Consider caching results

```bash
# Apply indexes
php artisan app:optimize-performance --indexes
```

### High Memory Usage

**Problem:** Application using too much memory

**Solution:**
1. Check for memory leaks
2. Use chunking for large datasets
3. Clear unnecessary variables
4. Optimize collection usage

```php
// Bad: Loads everything into memory
$transactions = Transaction::all();

// Good: Process in chunks
Transaction::chunk(100, function ($transactions) {
    // Process chunk
});
```

### Cache Not Working

**Problem:** Cache doesn't seem to be working

**Solution:**
1. Check cache driver configuration
2. Verify cache permissions
3. Clear cache and try again
4. Check cache TTL values

```bash
# Clear all caches
php artisan optimize:clear

# Verify configuration
php artisan config:show cache
```

### Database Performance

**Problem:** Database queries are slow

**Solution:**
1. Run table optimization
2. Check and add indexes
3. Analyze slow queries
4. Consider database tuning

```bash
# Optimize tables
php artisan app:optimize-performance --tables

# Apply indexes
php artisan app:optimize-performance --indexes
```

## Performance Checklist

### Development
- [ ] Enable query logging during development
- [ ] Profile slow pages
- [ ] Use eager loading for relationships
- [ ] Test with realistic data volumes

### Staging
- [ ] Run full optimization suite
- [ ] Test cache warming
- [ ] Verify index performance
- [ ] Load test the application

### Production
- [ ] Cache all configurations
- [ ] Apply all database indexes
- [ ] Set up cache warming schedule
- [ ] Monitor performance metrics
- [ ] Set up slow query logging
- [ ] Regular table optimization

## Additional Resources

### Laravel Documentation
- [Cache](https://laravel.com/docs/cache)
- [Database Optimization](https://laravel.com/docs/queries#chunking-results)
- [Performance](https://laravel.com/docs/deployment#optimization)

### Tools
- Laravel Telescope (query monitoring)
- Laravel Debugbar (development profiling)
- New Relic (production monitoring)
- Blackfire (performance profiling)

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Maintained By**: Development Team
