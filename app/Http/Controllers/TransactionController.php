<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $query = auth()->user()->transactions()->with('category');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->orderBy('date', 'desc')->paginate(15);
        $categories = auth()->user()->categories;

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create()
    {
        $categories = auth()->user()->categories;
        return view('transactions.create', compact('categories'));
    }

    public function store(TransactionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $this->transactionService->createTransaction($data);

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        $categories = auth()->user()->categories;
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(TransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        $this->transactionService->updateTransaction($transaction, $request->validated());

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        $this->transactionService->deleteTransaction($transaction);

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }

    public function dailySummary(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;
        $userId = auth()->id();

        // Get transactions for the selected date
        $transactions = Transaction::where('user_id', $userId)
            ->whereDate('date', $date)
            ->get();

        // Calculate totals
        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $income - $expenses;

        return response()->json([
            'date' => $date,
            'transaction_count' => $transactions->count(),
            'income' => $income,
            'income_formatted' => currency_format($income),
            'expenses' => $expenses,
            'expenses_formatted' => currency_format($expenses),
            'net_balance' => $netBalance,
            'net_balance_formatted' => currency_format($netBalance),
        ]);
    }
}
