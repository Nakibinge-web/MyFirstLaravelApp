<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'currency' => 'USD',
        ]);
        $this->actingAs($this->user);
    }

    public function test_can_update_currency_and_invalidates_cache(): void
    {
        // Populate cache first
        Cache::put("dashboard_data_user_{$this->user->id}", ['dummy' => 'data'], 300);
        $this->assertTrue(Cache::has("dashboard_data_user_{$this->user->id}"));

        $response = $this->postJson(route('dashboard.currency'), [
            'currency' => 'UGX',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'currency' => 'UGX',
                'currency_symbol' => 'UGX',
            ]);

        $this->user->refresh();
        $this->assertEquals('UGX', $this->user->currency);
        $this->assertFalse(Cache::has("dashboard_data_user_{$this->user->id}"));
    }

    public function test_refresh_returns_persisted_currency_and_symbol(): void
    {
        $this->user->update(['currency' => 'UGX']);

        $response = $this->getJson(route('dashboard.refresh'));

        $response->assertOk()
            ->assertJson([
                'currency' => 'UGX',
                'currency_symbol' => 'UGX',
            ]);
    }

    public function test_refresh_accepts_and_persists_currency_parameter(): void
    {
        $this->assertEquals('USD', $this->user->currency);

        $response = $this->getJson(route('dashboard.refresh', ['currency' => 'UGX']));

        $response->assertOk()
            ->assertJson([
                'currency' => 'UGX',
                'currency_symbol' => 'UGX',
            ]);

        $this->user->refresh();
        $this->assertEquals('UGX', $this->user->currency);
    }

    public function test_currency_helper_formats_multi_letter_currencies_with_space(): void
    {
        $this->user->update(['currency' => 'UGX']);

        $this->assertEquals('UGX 1,500.00', currency_format(1500));
        $this->assertEquals('$1,500.00', currency_format(1500, 'USD'));
        $this->assertEquals('1,500.00 €', currency_format(1500, 'EUR'));
    }

    public function test_refresh_ignores_invalid_currency_parameter(): void
    {
        $this->user->update(['currency' => 'UGX']);

        $response = $this->getJson(route('dashboard.refresh', ['currency' => 'INVALID']));

        $response->assertOk()
            ->assertJson([
                'currency' => 'UGX',
                'currency_symbol' => 'UGX',
            ]);

        $this->user->refresh();
        $this->assertEquals('UGX', $this->user->currency);
    }

    public function test_dashboard_renders_user_currency_and_isolated_refresh_formatter(): void
    {
        $this->user->update(['currency' => 'UGX']);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('<meta name="user-currency" content="UGX">', false)
            ->assertSee('UGX 0', false)
            ->assertSee('formatDashboardCurrency(data.monthlyStats.income)', false)
            ->assertSee('formatDashboardCurrency(data.monthlyStats.expenses)', false)
            ->assertSee('formatDashboardCurrency(data.monthlyStats.net_savings)', false)
            ->assertDontSee('addEventListener(\'click\', refreshDashboard)', false)
            ->assertDontSee('setInterval(refreshDashboard,', false);
    }
}

