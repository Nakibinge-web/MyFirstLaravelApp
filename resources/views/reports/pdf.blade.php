<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ $monthlyReport['period'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 30px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
        }
        .income { color: #10B981; }
        .expense { color: #EF4444; }
        .savings { color: #3B82F6; }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>💰 Financial Report</h1>
        <p>{{ $monthlyReport['period'] }}</p>
        <p>Generated on {{ date('F d, Y') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Income</div>
                <div class="summary-value income">${{ number_format($monthlyReport['income'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Expenses</div>
                <div class="summary-value expense">${{ number_format($monthlyReport['expenses'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Net Savings</div>
                <div class="summary-value savings">${{ number_format($monthlyReport['net_savings'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Savings Rate</div>
                <div class="summary-value">{{ $monthlyReport['savings_rate'] }}%</div>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown -->
    @if($expenseBreakdown->count() > 0)
    <div class="section">
        <div class="section-title">Expense Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php $totalExpenses = $expenseBreakdown->sum('amount'); @endphp
                @foreach($expenseBreakdown as $expense)
                <tr>
                    <td>{{ $expense['icon'] }} {{ $expense['category'] }}</td>
                    <td class="text-right">${{ number_format($expense['amount'], 2) }}</td>
                    <td class="text-right">{{ $totalExpenses > 0 ? number_format(($expense['amount'] / $totalExpenses) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #f9fafb;">
                    <td>Total</td>
                    <td class="text-right">${{ number_format($totalExpenses, 2) }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Income Breakdown -->
    @if($incomeBreakdown->count() > 0)
    <div class="section">
        <div class="section-title">Income Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php $totalIncome = $incomeBreakdown->sum('amount'); @endphp
                @foreach($incomeBreakdown as $income)
                <tr>
                    <td>{{ $income['icon'] }} {{ $income['category'] }}</td>
                    <td class="text-right">${{ number_format($income['amount'], 2) }}</td>
                    <td class="text-right">{{ $totalIncome > 0 ? number_format(($income['amount'] / $totalIncome) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #f9fafb;">
                    <td>Total</td>
                    <td class="text-right">${{ number_format($totalIncome, 2) }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Expenses -->
    @if($topExpenses->count() > 0)
    <div class="section">
        <div class="section-title">Top Expense Categories</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topExpenses as $expense)
                <tr>
                    <td>{{ $expense['icon'] }} {{ $expense['category'] }}</td>
                    <td class="text-right">{{ $expense['count'] }}</td>
                    <td class="text-right">${{ number_format($expense['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Transaction Statistics -->
    <div class="section">
        <div class="section-title">Transaction Statistics</div>
        <table>
            <tbody>
                <tr>
                    <td>Total Transactions</td>
                    <td class="text-right">{{ $stats['total_transactions'] }}</td>
                </tr>
                <tr>
                    <td>Income Transactions</td>
                    <td class="text-right">{{ $stats['income_transactions'] }}</td>
                </tr>
                <tr>
                    <td>Expense Transactions</td>
                    <td class="text-right">{{ $stats['expense_transactions'] }}</td>
                </tr>
                <tr>
                    <td>Average Income</td>
                    <td class="text-right">${{ number_format($stats['avg_income'], 2) }}</td>
                </tr>
                <tr>
                    <td>Average Expense</td>
                    <td class="text-right">${{ number_format($stats['avg_expense'], 2) }}</td>
                </tr>
                <tr>
                    <td>Largest Income</td>
                    <td class="text-right">${{ number_format($stats['largest_income'], 2) }}</td>
                </tr>
                <tr>
                    <td>Largest Expense</td>
                    <td class="text-right">${{ number_format($stats['largest_expense'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Personal Financial Tracker - Generated by {{ auth()->user()->name }}</p>
        <p>This report is confidential and intended for personal use only.</p>
    </div>
</body>
</html>
