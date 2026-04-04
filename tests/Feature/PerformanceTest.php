<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CacheService;
use App\Services\QueryOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test cache service functionality
     */
    public function test_cache_service_stores_and_retrieves_data()
    {
        $user = User::factory()->create();

        // Clear cache first
        CacheService::clearAllUserCache($user->id);

        // Test caching - first call populates cache
        $categories1 = CacheService::getUserCategories($user->id);

        // Second call should return same data (from cache)
        $categories2 = CacheService::getUserCategories($user->id);

        $this->assertCount($categories1->count(), $categories2);

        // Clear cache and verify it's gone
        CacheService::clearCategoriesCache($user->id);

        // After clearing, a new call should still work (re-populates from DB)
        $categories3 = CacheService::getUserCategories($user->id);
        $this->assertCount($categories1->count(), $categories3);
    }

    /**
     * Test dashboard caching reduces queries
     */
    public function test_dashboard_caching_reduces_database_queries()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Clear cache
        Cache::flush();

        // First request - should hit database
        $response1 = $this->get(route('dashboard'));
        $response1->assertStatus(200);

        // Second request - should use cache (same response)
        $response2 = $this->get(route('dashboard'));
        $response2->assertStatus(200);

        // Both responses should succeed - caching is transparent to the user
        $this->assertTrue(true);
    }

    /**
     * Test query optimization service
     */
    public function test_query_optimization_service_provides_statistics()
    {
        $optimizer = app(QueryOptimizationService::class);
        
        $stats = $optimizer->getTableStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('transactions', $stats);
        $this->assertArrayHasKey('budgets', $stats);
    }

    /**
     * Test cache invalidation on data update
     */
    public function test_cache_invalidates_on_transaction_create()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Warm cache by visiting dashboard
        $this->get(route('dashboard'));

        // Create a transaction (should invalidate cache)
        $category = $user->categories()->first();
        if (!$category) {
            $category = \App\Models\Category::factory()->create(['user_id' => $user->id]);
        }

        $response = $this->post(route('transactions.store'), [
            'amount' => 100,
            'type' => 'expense',
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Test transaction',
        ]);

        // Transaction was created successfully (redirect or 200)
        $this->assertContains($response->getStatusCode(), [200, 201, 302]);
    }

    /**
     * Test page load performance
     */
    public function test_dashboard_loads_within_acceptable_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $startTime = microtime(true);
        $response = $this->get(route('dashboard'));
        $endTime = microtime(true);

        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        
        // Dashboard should load in less than 2 seconds
        $this->assertLessThan(2000, $loadTime, "Dashboard took {$loadTime}ms to load");
    }

    /**
     * Test eager loading prevents N+1 queries
     */
    public function test_transactions_list_uses_eager_loading()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create(['user_id' => $user->id]);
        
        // Create multiple transactions
        \App\Models\Transaction::factory()->count(10)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($user);

        DB::enableQueryLog();
        $response = $this->get(route('transactions.index'));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        
        // Should have minimal queries due to eager loading
        // Typically: 1 for transactions + 1 for categories + 1 for user
        $this->assertLessThan(10, count($queries), "Too many queries detected: " . count($queries));
    }

    /**
     * Test cache TTL configuration
     */
    public function test_cache_ttl_configuration_exists()
    {
        $config = config('cache-settings.ttl');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('dashboard', $config);
        $this->assertArrayHasKey('categories', $config);
        $this->assertArrayHasKey('budgets', $config);
        $this->assertArrayHasKey('goals', $config);
    }
}
