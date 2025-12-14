<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'language'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::group(['prefix' => 'uploads'], function () {
            Route::get('{id}', 'Common\Uploads@get');
            Route::get('{id}/download', 'Common\Uploads@download');
        });

        Route::group(['middleware' => 'permission:read-admin-panel'], function () {
            Route::group(['prefix' => 'wizard'], function () {
                Route::get('/', 'Wizard\Companies@edit')->name('wizard.index');
                Route::get('companies', 'Wizard\Companies@edit')->name('wizard.companies.edit');
                Route::patch('companies', 'Wizard\Companies@update')->name('wizard.companies.update');

                Route::get('currencies', 'Wizard\Currencies@index')->name('wizard.currencies.index');
                Route::get('currencies/create', 'Wizard\Currencies@create')->name('wizard.currencies.create');
                Route::get('currencies/{currency}/edit', 'Wizard\Currencies@edit')->name('wizard.currencies.edit');
                Route::get('currencies/{currency}/enable', 'Wizard\Currencies@enable')->name('wizard.currencies.enable');
                Route::get('currencies/{currency}/disable', 'Wizard\Currencies@disable')->name('wizard.currencies.disable');
                Route::get('currencies/{currency}/delete', 'Wizard\Currencies@destroy')->name('wizard.currencies.delete');
                Route::post('currencies', 'Wizard\Currencies@store')->name('wizard.currencies.store');
                Route::patch('currencies/{currency}', 'Wizard\Currencies@update')->name('wizard.currencies.update');

                Route::get('finish', 'Wizard\Finish@index')->name('wizard.finish.index');
            });
        });

        Route::group(['middleware' => ['adminmenu', 'permission:read-admin-panel']], function () {
            Route::get('/', 'Common\Dashboard@index');

            Route::group(['prefix' => 'uploads'], function () {
                Route::delete('{id}', 'Common\Uploads@destroy');
            });

            Route::group(['prefix' => 'common'], function () {
                Route::get('companies/{company}/set', 'Common\Companies@set')->name('companies.switch');
                Route::get('companies/{company}/enable', 'Common\Companies@enable')->name('companies.enable');
                Route::get('companies/{company}/disable', 'Common\Companies@disable')->name('companies.disable');
                Route::resource('companies', 'Common\Companies');
                Route::get('dashboard/cashflow', 'Common\Dashboard@cashFlow')->name('dashboard.cashflow');
                Route::get('import/{group}/{type}', 'Common\Import@create')->name('import.create');
                Route::get('items/autocomplete', 'Common\Items@autocomplete')->name('items.autocomplete');
                Route::post('items/totalItem', 'Common\Items@totalItem')->middleware(['money'])->name('items.total');
                Route::get('items/{item}/duplicate', 'Common\Items@duplicate')->name('items.duplicate');
                Route::post('items/import', 'Common\Items@import')->name('items.import');
                Route::get('items/export', 'Common\Items@export')->name('items.export');
                Route::get('items/{item}/enable', 'Common\Items@enable')->name('items.enable');
                Route::get('items/{item}/disable', 'Common\Items@disable')->name('items.disable');
                Route::resource('items', 'Common\Items', ['middleware' => ['money']]);
                Route::get('search/search', 'Common\Search@search')->name('search.search');
                Route::resource('search', 'Common\Search');
            });

            Route::group(['prefix' => 'auth'], function () {
                Route::get('logout', 'Auth\Login@destroy')->name('logout');
                Route::get('users/autocomplete', 'Auth\Users@autocomplete');
                Route::get('users/{user}/read-items', 'Auth\Users@readItemsOutOfStock');
                Route::get('users/{user}/enable', 'Auth\Users@enable')->name('users.enable');
                Route::get('users/{user}/disable', 'Auth\Users@disable')->name('users.disable');
                Route::resource('users', 'Auth\Users');
                Route::resource('roles', 'Auth\Roles');
                Route::resource('permissions', 'Auth\Permissions');
            });

            Route::name('incomes.')->prefix('incomes')->group(function () {

                Route::resource('receivables', 'Incomes\Receivables', ['middleware' => ['dateformat', 'money']]);
                Route::get('receivables/{receivable}/duplicate', 'Incomes\Receivables@duplicate');

                Route::get('revenues/{revenue}/duplicate', 'Incomes\Revenues@duplicate');
                Route::post('revenues/import', 'Incomes\Revenues@import')->name('revenues.import');
                Route::get('revenues/export', 'Incomes\Revenues@export')->name('revenues.export');
                Route::resource('revenues', 'Incomes\Revenues', ['middleware' => ['dateformat', 'money']]);
                Route::get('customers/currency', 'Incomes\Customers@currency');
                Route::get('customers/{customer}/duplicate', 'Incomes\Customers@duplicate');
                Route::post('customers/customer', 'Incomes\Customers@customer');
                Route::post('customers/field', 'Incomes\Customers@field');
                Route::post('customers/import', 'Incomes\Customers@import')->name('customers.import');
                Route::get('customers/export', 'Incomes\Customers@export')->name('customers.export');
                Route::get('customers/{customer}/enable', 'Incomes\Customers@enable')->name('customers.enable');
                Route::get('customers/{customer}/disable', 'Incomes\Customers@disable')->name('customers.disable');
                Route::resource('customers', 'Incomes\Customers');
            });

            Route::name('expenses.')->prefix('expenses')->group(function () {

                Route::resource('payables', 'Expenses\Payables', ['middleware' => ['dateformat', 'money']]);
                Route::get('payables/{payable}/duplicate', 'Expenses\Payables@duplicate');

                Route::get('payments/{payment}/duplicate', 'Expenses\Payments@duplicate');
                Route::post('payments/import', 'Expenses\Payments@import')->name('payments.import');
                Route::get('payments/export', 'Expenses\Payments@export')->name('payments.export');
                Route::resource('payments', 'Expenses\Payments', ['middleware' => ['dateformat', 'money']]);
                Route::get('vendors/currency', 'Expenses\Vendors@currency');
                Route::get('vendors/{vendor}/duplicate', 'Expenses\Vendors@duplicate');
                Route::post('vendors/vendor', 'Expenses\Vendors@vendor');
                Route::post('vendors/import', 'Expenses\Vendors@import')->name('vendors.import');
                Route::get('vendors/export', 'Expenses\Vendors@export')->name('vendors.export');
                Route::get('vendors/{vendor}/enable', 'Expenses\Vendors@enable')->name('vendors.enable');
                Route::get('vendors/{vendor}/disable', 'Expenses\Vendors@disable')->name('vendors.disable');
                Route::resource('vendors', 'Expenses\Vendors');
            });

            Route::name('banking.')->prefix('banking')->group(function () {
                Route::get('accounts/currency', 'Banking\Accounts@currency')->name('accounts.currency');
                Route::get('accounts/balance', 'Banking\Accounts@balance')->name('accounts.balance');
                Route::get('accounts/{account}/enable', 'Banking\Accounts@enable')->name('accounts.enable');
                Route::get('accounts/{account}/disable', 'Banking\Accounts@disable')->name('accounts.disable');
                Route::resource('accounts', 'Banking\Accounts', ['middleware' => ['dateformat', 'money']]);
                Route::resource('transactions', 'Banking\Transactions');
                Route::resource('transfers', 'Banking\Transfers', ['middleware' => ['dateformat', 'money']]);
                Route::post('reconciliations/calculate', 'Banking\Reconciliations@calculate')->middleware(['money']);
                Route::patch('reconciliations/calculate', 'Banking\Reconciliations@calculate')->middleware(['money']);
                Route::resource('reconciliations', 'Banking\Reconciliations', ['middleware' => ['dateformat', 'money']]);
            });

            Route::group(['prefix' => 'reports'], function () {
//                Route::resource('income-summary', 'Reports\IncomeSummary');
                Route::resource('expense-summary', 'Reports\ExpenseSummary');
//                Route::resource('income-expense-summary', 'Reports\IncomeExpenseSummary');
                Route::resource('profit-loss', 'Reports\ProfitLoss');
            });

            Route::group(['prefix' => 'settings'], function () {
                Route::post('categories/category', 'Settings\Categories@category');
                Route::get('categories/{category}/enable', 'Settings\Categories@enable')->name('categories.enable');
                Route::get('categories/{category}/disable', 'Settings\Categories@disable')->name('categories.disable');
                Route::resource('categories', 'Settings\Categories');
                Route::get('currencies/currency', 'Settings\Currencies@currency');
                Route::get('currencies/config', 'Settings\Currencies@config');
                Route::get('currencies/{currency}/enable', 'Settings\Currencies@enable')->name('currencies.enable');
                Route::get('currencies/{currency}/disable', 'Settings\Currencies@disable')->name('currencies.disable');
                Route::resource('currencies', 'Settings\Currencies');
                Route::get('settings', 'Settings\Settings@edit');
                Route::patch('settings', 'Settings\Settings@update');
            });

            Route::group(['as' => 'modals.', 'prefix' => 'modals'], function () {
                Route::resource('categories', 'Modals\Categories');
                Route::resource('customers', 'Modals\Customers');
                Route::resource('vendors', 'Modals\Vendors');
            });

            /* @deprecated */
            Route::post('items/items/totalItem', 'Common\Items@totalItem');
        });

        Route::group(['middleware' => ['customermenu', 'permission:read-customer-panel']], function () {
            Route::name('customers.')->prefix('customers')->group(function () {
                Route::get('/', 'Customers\Dashboard@index');

                Route::resource('payments', 'Customers\Payments');
                Route::get('transactions', 'Customers\Transactions@index')->name('customers.transactions');
                Route::resource('profile', 'Customers\Profile');

                Route::get('logout', 'Auth\Login@destroy')->name('customer_logout');
            });
        });
    });

    Route::group(['middleware' => 'guest'], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::get('login', 'Auth\Login@create')->name('login');
            Route::post('login', 'Auth\Login@store');

            Route::get('forgot', 'Auth\Forgot@create')->name('forgot');
            Route::post('forgot', 'Auth\Forgot@store');

            //Route::get('reset', 'Auth\Reset@create');
            Route::get('reset/{token}', 'Auth\Reset@create')->name('reset');
            Route::post('reset', 'Auth\Reset@store');
        });

        Route::group(['middleware' => 'install'], function () {
            Route::group(['prefix' => 'install'], function () {
                Route::get('/', 'Install\Requirements@show');
                Route::get('requirements', 'Install\Requirements@show');

                Route::get('language', 'Install\Language@create');
                Route::post('language', 'Install\Language@store');

                Route::get('database', 'Install\Database@create');
                Route::post('database', 'Install\Database@store');

                Route::get('settings', 'Install\Settings@create');
                Route::post('settings', 'Install\Settings@store');
            });
        });
    });
});
