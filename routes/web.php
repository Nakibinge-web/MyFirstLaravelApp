<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes with rate limiting
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');
    Route::post('/dashboard/currency', [DashboardController::class, 'updateCurrency'])->name('dashboard.currency');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:10,1');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update')->middleware('throttle:5,1');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:3,1');
    
    Route::get('transactions/daily-summary', [\App\Http\Controllers\TransactionController::class, 'dailySummary'])->name('transactions.daily-summary');
    Route::get('transactions/weekly-summary', [\App\Http\Controllers\TransactionController::class, 'weeklySummary'])->name('transactions.weekly-summary');
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
    Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['show']);
    Route::resource('budgets', \App\Http\Controllers\BudgetController::class)->except(['show']);
    Route::resource('goals', \App\Http\Controllers\GoalController::class);
    Route::post('goals/{goal}/progress', [\App\Http\Controllers\GoalController::class, 'updateProgress'])->name('goals.progress');
    Route::post('goals/{goal}/toggle', [\App\Http\Controllers\GoalController::class, 'toggleStatus'])->name('goals.toggle');
    
    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/yearly', [\App\Http\Controllers\ReportController::class, 'yearly'])->name('reports.yearly');
    Route::get('reports/export/pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('reports/export/csv', [\App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export.csv');
    
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('notifications/check-alerts', [\App\Http\Controllers\NotificationController::class, 'checkAlerts'])->name('notifications.check');
    
    // Demo page for UI/UX features
    Route::get('/demo/features', function () {
        return view('demo.features');
    })->name('demo.features');
});
