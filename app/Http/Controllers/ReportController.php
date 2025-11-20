<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        // Get monthly report
        $monthlyReport = $this->reportService->getMonthlyReport(auth()->id(), $year, $month);

        // Get category breakdown
        $expenseBreakdown = $this->reportService->getCategoryBreakdown(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date'],
            'expense'
        );

        $incomeBreakdown = $this->reportService->getCategoryBreakdown(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date'],
            'income'
        );

        // Get income vs expense trend (last 6 months)
        $trend = $this->reportService->getIncomeVsExpenseTrend(auth()->id(), 6);

        // Get top expense categories
        $topExpenses = $this->reportService->getTopExpenseCategories(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date'],
            5
        );

        // Get transaction stats
        $stats = $this->reportService->getTransactionStats(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date']
        );

        return view('reports.index', compact(
            'monthlyReport',
            'expenseBreakdown',
            'incomeBreakdown',
            'trend',
            'topExpenses',
            'stats',
            'year',
            'month'
        ));
    }

    public function yearly(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $yearlyReport = $this->reportService->getYearlyReport(auth()->id(), $year);

        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();

        $expenseBreakdown = $this->reportService->getCategoryBreakdown(
            auth()->id(),
            $startDate,
            $endDate,
            'expense'
        );

        $incomeBreakdown = $this->reportService->getCategoryBreakdown(
            auth()->id(),
            $startDate,
            $endDate,
            'income'
        );

        $topExpenses = $this->reportService->getTopExpenseCategories(
            auth()->id(),
            $startDate,
            $endDate,
            10
        );

        return view('reports.yearly', compact(
            'yearlyReport',
            'expenseBreakdown',
            'incomeBreakdown',
            'topExpenses',
            'year'
        ));
    }

    public function exportPdf(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $monthlyReport = $this->reportService->getMonthlyReport(auth()->id(), $year, $month);

        $expenseBreakdown = $this->reportService->getCategoryBreakdown(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date'],
            'expense'
        );

        $incomeBreakdown = $this->reportService->getCategoryBreakdown(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date'],
            'income'
        );

        $topExpenses = $this->reportService->getTopExpenseCategories(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date'],
            5
        );

        $stats = $this->reportService->getTransactionStats(
            auth()->id(),
            $monthlyReport['start_date'],
            $monthlyReport['end_date']
        );

        $pdf = \PDF::loadView('reports.pdf', compact(
            'monthlyReport',
            'expenseBreakdown',
            'incomeBreakdown',
            'topExpenses',
            'stats'
        ));

        return $pdf->download('financial-report-' . $monthlyReport['period'] . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $transactions = \App\Models\Transaction::where('user_id', auth()->id())
            ->whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $filename = 'transactions-' . $startDate->format('Y-m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Date', 'Category', 'Type', 'Description', 'Amount']);

            // Add data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->category->name,
                    ucfirst($transaction->type),
                    $transaction->description,
                    number_format($transaction->amount, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
