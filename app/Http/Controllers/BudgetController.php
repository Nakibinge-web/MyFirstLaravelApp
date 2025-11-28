<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Services\BudgetService;

class BudgetController extends Controller
{
    protected $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    public function index()
    {
        $budgets = auth()->user()->budgets()->with('category')->orderBy('start_date', 'desc')->get();
        
        $budgetsWithUtilization = $budgets->map(function ($budget) {
            $utilization = $this->budgetService->calculateUtilization($budget);
            $budget->utilization = $utilization;
            return $budget;
        });

        return view('budgets.index', compact('budgetsWithUtilization'));
    }

    public function create()
    {
        $categories = auth()->user()->categories()->where('type', 'expense')->get();
        return view('budgets.create', compact('categories'));
    }

    public function store(BudgetRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($data['period'] === 'weekly') {
            $this->budgetService->createWeeklyBudget($data);
        } elseif ($data['period'] === 'monthly') {
            $this->budgetService->createMonthlyBudget($data);
        } else {
            $this->budgetService->createYearlyBudget($data);
        }

        return redirect()->route('budgets.index')->with('success', 'Budget created successfully.');
    }

    public function edit(Budget $budget)
    {
        $this->authorize('update', $budget);
        $categories = auth()->user()->categories()->where('type', 'expense')->get();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(BudgetRequest $request, Budget $budget)
    {
        $this->authorize('update', $budget);
        
        $data = $request->validated();
        
        // Recalculate dates based on period
        if ($data['period'] === 'weekly') {
            $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
            $endDate = $startDate->copy()->addDays(6)->endOfDay();
        } elseif ($data['period'] === 'monthly') {
            $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
            $endDate = $startDate->copy()->addDays(29)->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
            $endDate = $startDate->copy()->addDays(364)->endOfDay();
        }

        $budget->update([
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'period' => $data['period'],
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return redirect()->route('budgets.index')->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully.');
    }
}
