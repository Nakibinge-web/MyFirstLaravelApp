#!/bin/bash

# =============================================================================
# Redis Performance Monitor
# =============================================================================
# Monitor Redis performance and cache statistics
# =============================================================================

echo "🔍 Redis Performance Monitor"
echo "============================"
echo ""

# Check if Redis container is running
if ! docker-compose ps redis | grep -q "Up"; then
    echo "❌ Redis container is not running!"
    exit 1
fi

echo "📊 Redis Statistics:"
echo "-------------------"

# Get Redis info
docker-compose exec -T redis redis-cli info stats | grep -E "(keyspace_hits|keyspace_misses|used_memory_human|connected_clients)"

echo ""
echo "🔑 Cache Keys:"
echo "-------------"

# Count keys by pattern
TOTAL_KEYS=$(docker-compose exec -T redis redis-cli dbsize)
echo "Total Keys: $TOTAL_KEYS"

# Show cache hit ratio
HITS=$(docker-compose exec -T redis redis-cli info stats | grep keyspace_hits | cut -d: -f2 | tr -d '\r')
MISSES=$(docker-compose exec -T redis redis-cli info stats | grep keyspace_misses | cut -d: -f2 | tr -d '\r')

if [ "$HITS" -gt 0 ] || [ "$MISSES" -gt 0 ]; then
    TOTAL=$((HITS + MISSES))
    HIT_RATIO=$(echo "scale=2; $HITS * 100 / $TOTAL" | bc -l 2>/dev/null || echo "0")
    echo "Cache Hit Ratio: ${HIT_RATIO}%"
else
    echo "Cache Hit Ratio: No data yet"
fi

echo ""
echo "💾 Memory Usage:"
echo "---------------"
docker-compose exec -T redis redis-cli info memory | grep -E "(used_memory_human|used_memory_peak_human|maxmemory_human)"

echo ""
echo "🔄 Recent Activity:"
echo "------------------"
echo "Recent cache keys (last 10):"
docker-compose exec -T redis redis-cli keys "*" | head -10

echo ""
echo "✅ Redis monitoring complete!"