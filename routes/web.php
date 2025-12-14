<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PayablesController;
use App\Http\Controllers\ReceivablesController;
use App\Http\Controllers\Transactions\PaymentsController;
use App\Http\Controllers\Transactions\RevenuesController;
use App\Http\Controllers\Transactions\TransfersController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Redirect::route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('forecast-chart', [DashboardController::class, 'forecast_chart'])->name('forecast_chart');
    Route::get('cashflow-chart', [DashboardController::class, 'cashflow_chart'])->name('cashflow_chart');
    Route::post('set-active-company', [DashboardController::class, 'set_active_company'])->name('set_active_company');

    Route::get('transactions', [TransactionsController::class, 'index'])->name('transactions.index');

    Route::post('transactions/payments', [PaymentsController::class, 'create'])->name('transactions.payments.create');
    Route::get('transactions/payments/new', [PaymentsController::class, 'new'])->name('transactions.payments.new');
    Route::get('transactions/payments/edit/{payment}', [PaymentsController::class, 'edit'])->name('transactions.payments.edit');
    Route::patch('transactions/payments/{payment}', [PaymentsController::class, 'update'])->name('transactions.payments.update');
    Route::delete('transactions/payments/{payment}', [PaymentsController::class, 'destroy'])->name('transactions.payments.delete');

    Route::post('transactions/revenues', [RevenuesController::class, 'create'])->name('transactions.revenues.create');
    Route::get('transactions/revenues/new', [RevenuesController::class, 'new'])->name('transactions.revenues.new');
    Route::get('transactions/revenues/edit/{revenue}', [RevenuesController::class, 'edit'])->name('transactions.revenues.edit');
    Route::patch('transactions/revenues/{revenue}', [RevenuesController::class, 'update'])->name('transactions.revenues.update');
    Route::delete('transactions/revenues/{revenue}', [RevenuesController::class, 'destroy'])->name('transactions.revenues.delete');

    Route::get('transactions/transfers/new', [TransfersController::class, 'new'])->name('transactions.transfers.new');
    Route::get('transactions/transfers/edit/{transfer}', [TransfersController::class, 'edit'])->name('transactions.transfers.edit');
    Route::post('transactions/transfers', [TransfersController::class, 'create'])->name('transactions.transfers.create');
    Route::patch('transactions/transfers/{transfer}', [TransfersController::class, 'update'])->name('transactions.transfers.update');
    Route::delete('transactions/transfers/{transfer}', [TransfersController::class, 'destroy'])->name('transactions.transfers.delete');

    Route::get('accounts', [AccountsController::class, 'index'])->name('accounts.index');
    Route::get('accounts/new', [AccountsController::class, 'new'])->name('accounts.new');
    Route::post('accounts', [AccountsController::class, 'create'])->name('accounts.create');
    Route::get('accounts/{account}', [AccountsController::class, 'edit'])->name('accounts.edit');
    Route::patch('accounts/{account}', [AccountsController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{account}', [AccountsController::class, 'destroy'])->name('accounts.delete');

    Route::get('categories', [CategoriesController::class, 'index'])->name('categories.index');
    Route::get('categories/new', [CategoriesController::class, 'new'])->name('categories.new');
    Route::get('categories/{category}', [CategoriesController::class, 'edit'])->name('categories.edit');
    Route::post('categories', [CategoriesController::class, 'create'])->name('categories.create');
    Route::patch('categories/{category}', [CategoriesController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoriesController::class, 'destroy'])->name('categories.delete');

    Route::get('payables', [PayablesController::class, 'index'])->name('payables.index');
    Route::get('payables/new', [PayablesController::class, 'new'])->name('payables.new');
    Route::post('payables', [PayablesController::class, 'create'])->name('payables.create');
    Route::get('payables/{payable}', [PayablesController::class, 'edit'])->name('payables.edit');
    Route::patch('payables/{payable}', [PayablesController::class, 'update'])->name('payables.update');
    Route::delete('payables/{payable}', [PayablesController::class, 'destroy'])->name('payables.delete');

    Route::get('receivables', [ReceivablesController::class, 'index'])->name('receivables.index');
    Route::get('receivables/new', [ReceivablesController::class, 'new'])->name('receivables.new');
    Route::get('receivables/{receivable}', [ReceivablesController::class, 'edit'])->name('receivables.edit');
    Route::post('receivables', [ReceivablesController::class, 'create'])->name('receivables.create');
    Route::patch('receivables/{receivable}', [ReceivablesController::class, 'update'])->name('receivables.update');
    Route::delete('receivables/{receivable}', [ReceivablesController::class, 'destroy'])->name('receivables.delete');

    Route::get('companies', [CompaniesController::class, 'index'])->name('companies.index');
    Route::get('companies/new', [CompaniesController::class, 'new'])->name('companies.new');
    Route::post('companies', [CompaniesController::class, 'create'])->name('companies.create');
    Route::get('companies/{company}', [CompaniesController::class, 'edit'])->name('companies.edit');
    Route::patch('companies/{company}', [CompaniesController::class, 'update'])->name('companies.update');
    Route::delete('companies/{company}', [CompaniesController::class, 'destroy'])->name('companies.delete');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
