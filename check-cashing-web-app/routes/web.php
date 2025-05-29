<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Customers\CustomerIndexController;
use App\Http\Controllers\Customers\CustomerCreateController;
use App\Http\Controllers\Customers\CustomerStoreController;
use App\Http\Controllers\Customers\CustomerEditController;
use App\Http\Controllers\Customers\CustomerUpdateController;
use App\Http\Controllers\Customers\CustomerDestroyController;
use App\Http\Controllers\Checks\CheckIndexController;
use App\Http\Controllers\Checks\CheckCreateController;
use App\Http\Controllers\Checks\CheckStoreController;
use App\Http\Controllers\Checks\CheckEditController;
use App\Http\Controllers\Checks\CheckUpdateController;
use App\Http\Controllers\Checks\CheckDestroyController;
use App\Http\Controllers\Transactions\TransactionIndexController;
use App\Http\Controllers\Transactions\TransactionCreateController;
use App\Http\Controllers\Transactions\TransactionStoreController;
use App\Http\Controllers\Transactions\TransactionEditController;
use App\Http\Controllers\Transactions\TransactionUpdateController;
use App\Http\Controllers\Transactions\TransactionDestroyController;
use App\Http\Controllers\Dashboard\DashboardInformationController;



Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('dashboard')->middleware(['auth', 'verified'])->group(function () {
    Route::get('getDailyPayout', [DashboardInformationController::class, 'getDailyPayout'])->name('dashboard.getDailyPayout');
    Route::get('getWeeklyPayout', [DashboardInformationController::class, 'getWeeklyPayout'])->name('dashboard.getWeeklyPayout');
    Route::get('getMonthlyPayout', [DashboardInformationController::class, 'getMonthlyPayout'])->name('dashboard.getMonthlyPayout');
    Route::get('getYearlyPayout', [DashboardInformationController::class, 'getYearlyPayout'])->name('dashboard.getYearlyPayout');
    Route::get('getDailyEarnings', [DashboardInformationController::class, 'getDailyEarnings'])->name('dashboard.getDailyEarnings');
    Route::get('getWeeklyEarnings', [DashboardInformationController::class, 'getWeeklyEarnings'])->name('dashboard.getWeeklyEarnings');
    Route::get('getMonthlyEarnings', [DashboardInformationController::class, 'getMonthlyEarnings'])->name('dashboard.getMonthlyEarnings');
    Route::get('getYearlyEarnings', [DashboardInformationController::class, 'getYearlyEarnings'])->name('dashboard.getYearlyEarnings');
    Route::get('getRecentTransactions', [DashboardInformationController::class, 'getRecentTransactions'])->name('dashboard.getRecentTransactions');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('customers', CustomerIndexController::class)->name('customers.index');
    Route::get('customers/create', CustomerCreateController::class)->name('customers.create');
    Route::post('customers', CustomerStoreController::class)->name('customers.store');
    Route::get('customers/{customer}/edit', CustomerEditController::class)->name('customers.edit');
    Route::put('customers/{customer}', CustomerUpdateController::class)->name('customers.update');
    Route::delete('customers/{customer}', CustomerDestroyController::class)->name('customers.destroy');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('checks', CheckIndexController::class)->name('checks.index');
    Route::get('checks/create', CheckCreateController::class)->name('checks.create');
    Route::post('checks', CheckStoreController::class)->name('checks.store');
    Route::get('checks/{check}/edit', CheckEditController::class)->name('checks.edit');
    Route::put('checks/{check}', CheckUpdateController::class)->name('checks.update');
    Route::delete('checks/{check}', CheckDestroyController::class)->name('checks.destroy');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('transactions', TransactionIndexController::class)->name('transactions.index');
    Route::get('transactions/create', TransactionCreateController::class)->name('transactions.create');
    Route::post('transactions', TransactionStoreController::class)->name('transactions.store');
    Route::get('transactions/{transaction}/edit', TransactionEditController::class)->name('transactions.edit');
    Route::put('transactions/{transaction}', TransactionUpdateController::class)->name('transactions.update');
    Route::delete('transactions/{transaction}', TransactionDestroyController::class)->name('transactions.destroy');
    Route::get('transactions/getRecentTransactions', [TransactionIndexController::class, 'getRecentTransactions'])->name('transactions.getRecentTransactions');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
