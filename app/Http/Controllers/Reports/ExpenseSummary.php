<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Banking\Account;
use App\Models\Expense\Payment;
use App\Models\Expense\Vendor;
use App\Models\Setting\Category;
use App\Utilities\Recurring;
use Charts;
use Date;

class ExpenseSummary extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $dates = $totals = $expenses = $expenses_graph = $categories = [];

        $status = request('status');
        $year = request('year', Date::now()->year);

        $categories = Category::enabled()->type('expense')->pluck('name', 'id')->toArray();

        if ($categories_filter = request('categories')) {
            $cats = collect($categories)->filter(function ($value, $key) use ($categories_filter) {
                return in_array($key, $categories_filter);
            });
        } else {
            $cats = $categories;
        }

        // Dates
        for ($j = 1; $j <= 12; $j++) {
            $dates[$j] = Date::parse($year . '-' . $j)->format('F');

            $expenses_graph[Date::parse($year . '-' . $j)->format('F-Y')] = 0;

            // Totals
            $totals[$dates[$j]] = array(
                'amount' => 0,
                'currency_code' => setting('general.default_currency'),
                'currency_rate' => 1
            );

            foreach ($cats as $category_id => $category_name) {
                $expenses[$category_id][$dates[$j]] = array(
                    'category_id' => $category_id,
                    'name' => $category_name,
                    'amount' => 0,
                    'currency_code' => setting('general.default_currency'),
                    'currency_rate' => 1
                );
            }
        }

        $payments = Payment::monthsOfYear('paid_at')->account(request('accounts'))->vendor(request('vendors'))->isNotTransfer()->get();

        switch ($status) {
            case 'paid':
                // Payments
                $this->setAmount($expenses_graph, $totals, $expenses, $payments, 'payment', 'paid_at');
                break;
            case 'upcoming':
                // Payments
                Recurring::reflect($payments, 'payment', 'paid_at', $status);
                $this->setAmount($expenses_graph, $totals, $expenses, $payments, 'payment', 'paid_at');
                break;
            default:
                // Payments
                Recurring::reflect($payments, 'payment', 'paid_at', $status);
                $this->setAmount($expenses_graph, $totals, $expenses, $payments, 'payment', 'paid_at');
                break;
        }

        $statuses = collect([
            'all' => trans('general.all'),
            'paid' => trans('invoices.paid'),
            'upcoming' => trans('dashboard.payables'),
        ]);

        $accounts = Account::enabled()->pluck('name', 'id')->toArray();
        $vendors = Vendor::enabled()->pluck('name', 'id')->toArray();

        // Check if it's a print or normal request
        if (request('print')) {
            $chart_template = 'vendor.consoletvs.charts.chartjs.multi.line_print';
            $view_template = 'reports.expense_summary.print';
        } else {
            $chart_template = 'vendor.consoletvs.charts.chartjs.multi.line';
            $view_template = 'reports.expense_summary.index';
        }

        // Expenses chart
        $chart = Charts::multi('line', 'chartjs')
            ->dimensions(0, 300)
            ->colors(['#F56954'])
            ->dataset(trans_choice('general.expenses', 1), $expenses_graph)
            ->labels($dates)
            ->credits(false)
            ->view($chart_template);

        return view($view_template, compact('chart', 'dates', 'categories', 'statuses', 'accounts', 'vendors', 'expenses', 'totals'));
    }

    private function setAmount(&$graph, &$totals, &$expenses, $items, $type, $date_field)
    {
        foreach ($items as $item) {
            switch ($item->getTable()) {
                case 'bill_payments':
                    $bill = $item->bill;

                    if ($vendors = request('vendors')) {
                        if (!in_array($bill->vendor_id, $vendors)) {
                            continue;
                        }
                    }

                    $item->category_id = $bill->category_id;
                    break;
                case 'bills':
                    if ($accounts = request('accounts')) {
                        foreach ($item->payments as $payment) {
                            if (!in_array($payment->account_id, $accounts)) {
                                continue 2;
                            }
                        }
                    }
                    break;
            }

            $date = Date::parse($item->$date_field)->format('F');

            if (!isset($expenses[$item->category_id])) {
                continue;
            }

            $amount = $item->amount;

            // Forecasting
            if (($type == 'bill') && ($date_field == 'due_at')) {
                foreach ($item->payments as $payment) {
                    $amount -= $payment->amount;
                }
            }

            $expenses[$item->category_id][$date]['amount'] += $amount;
            $expenses[$item->category_id][$date]['currency_code'] = $item->currency_code;
            $expenses[$item->category_id][$date]['currency_rate'] = $item->currency_rate;

            $graph[Date::parse($item->$date_field)->format('F-Y')] += $amount;

            $totals[$date]['amount'] += $amount;
        }
    }
}
