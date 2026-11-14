<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use App\Services\DashboardService;
use App\Services\GoalService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $budgetService;
    protected $goalService;

    public function __construct(
        DashboardService $dashboardService,
        BudgetService $budgetService,
        GoalService $goalService
    ) {
        $this->dashboardService = $dashboardService;
        $this->budgetService = $budgetService;
        $this->goalService = $goalService;
    }

    public function index()
    {
        $userId = auth()->id();
        $cacheKey = "dashboard_data_user_{$userId}";
        $cacheTTL = config('cache-settings.ttl.dashboard', 300);

        // Cache dashboard data for 5 minutes
        $dashboardData = Cache::remember($cacheKey, $cacheTTL, function () use ($userId) {
            // Get monthly stats
            $monthlyStats = $this->dashboardService->getMonthlyStats($userId);

            // Get recent transactions (last 5)
            $recentTransactions = $this->dashboardService->getRecentTransactions($userId, 5);

            // Get active budgets with utilization
            $budgets = $this->dashboardService->getActiveBudgets($userId);
            $budgetsWithUtilization = $budgets->map(function ($budget) {
                $budget->utilization = $this->budgetService->calculateUtilization($budget);
                return $budget;
            });

            // Get active goals with progress
            $goals = $this->dashboardService->getActiveGoals($userId, 5);
            $goalsWithProgress = $goals->map(function ($goal) {
                $goal->progress = $this->goalService->calculateProgress($goal);
                return $goal;
            });

            // Get net worth
            $netWorth = $this->dashboardService->calculateNetWorth($userId);

            // Get quick stats
            $quickStats = $this->dashboardService->getQuickStats($userId);

            // Get spending trend (last 7 days)
            $spendingTrend = $this->dashboardService->getSpendingTrend($userId, 7);

            return compact(
                'monthlyStats',
                'recentTransactions',
                'budgetsWithUtilization',
                'goalsWithProgress',
                'netWorth',
                'quickStats',
                'spendingTrend'
            );
        });

        return view('dashboard', $dashboardData);
    }

    public function refresh()
    {
        $userId = auth()->id();
        $user = auth()->user();

        // If client requested a specific currency, update the user profile
        if (request()->has('currency')) {
            $reqCurrency = strtoupper(trim((string) request('currency')));
            if (preg_match('/^[A-Z]{3}$/', $reqCurrency) && $user && $user->currency !== $reqCurrency) {
                $user->currency = $reqCurrency;
                $user->save();
            }
        }
        
        // Clear cache for this user
        Cache::forget("dashboard_data_user_{$userId}");

        // Get monthly stats
        $monthlyStats = $this->dashboardService->getMonthlyStats($userId);

        // Get recent transactions (last 5)
        $recentTransactions = $this->dashboardService->getRecentTransactions($userId, 5);

        // Get active budgets with utilization
        $budgets = $this->dashboardService->getActiveBudgets($userId);
        $budgetsWithUtilization = $budgets->map(function ($budget) {
            $budget->utilization = $this->budgetService->calculateUtilization($budget);
            return $budget;
        });

        // Get active goals with progress
        $goals = $this->dashboardService->getActiveGoals($userId, 5);
        $goalsWithProgress = $goals->map(function ($goal) {
            $goal->progress = $this->goalService->calculateProgress($goal);
            return $goal;
        });

        // Get net worth
        $netWorth = $this->dashboardService->calculateNetWorth($userId);

        // Get quick stats
        $quickStats = $this->dashboardService->getQuickStats($userId);

        // Get spending trend
        $spendingTrend = $this->dashboardService->getSpendingTrend($userId, 7);

        $activeCurrency = $user->currency ?? request('currency') ?? 'USD';

        return response()->json([
            'currency' => $activeCurrency,
            'currency_symbol' => currency_symbol($activeCurrency),
            'monthlyStats' => $monthlyStats,
            'recentTransactions' => $recentTransactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'category_name' => $t->category->name,
                    'category_icon' => $t->category->icon,
                    'type' => $t->type,
                    'amount' => $t->amount,
                    'description' => $t->description,
                    'date' => $t->date->format('M d, Y'),
                ];
            }),
            'budgets' => $budgetsWithUtilization->take(3)->map(function ($b) {
                return [
                    'id' => $b->id,
                    'category_name' => $b->category->name,
                    'category_icon' => $b->category->icon,
                    'amount' => $b->amount,
                    'utilization' => $b->utilization,
                ];
            }),
            'goals' => $goalsWithProgress->take(3)->map(function ($g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'current_amount' => $g->current_amount,
                    'target_amount' => $g->target_amount,
                    'progress' => $g->progress,
                ];
            }),
            'netWorth' => $netWorth,
            'quickStats' => $quickStats,
            'spendingTrend' => $spendingTrend,
        ]);
    }

    public function updateCurrency()
    {
        $validated = request()->validate([
            'currency' => 'required|string|size:3',
        ]);

        $user = auth()->user();
        $currency = strtoupper($validated['currency']);
        $user->currency = $currency;
        $user->save();

        // Invalidate cached dashboard data so fresh calculations use the new currency
        Cache::forget("dashboard_data_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Currency updated successfully',
            'currency' => $user->currency,
            'currency_symbol' => currency_symbol($user->currency),
        ]);
    }
}
