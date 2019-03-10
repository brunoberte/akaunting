<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Income\Revenue;
use App\Models\Expense\Payment;
use App\Models\Setting\Category;
use Charts;
use Date;

class ProfitLoss extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $dates = $totals = $compares = $categories = [];

        $status = request('status');
        $year = request('year', Date::now()->year);

        $income_categories = Category::enabled()->type('income')->pluck('name', 'id')->toArray();

        $expense_categories = Category::enabled()->type('expense')->pluck('name', 'id')->toArray();

        // Dates
        for ($j = 1; $j <= 12; $j++) {
            $dates[$j] = Date::parse($year . '-' . $j)->quarter;

            // Totals
            $totals[$dates[$j]] = array(
                'amount' => 0,
                'currency_code' => setting('general.default_currency'),
                'currency_rate' => 1
            );

            foreach ($income_categories as $category_id => $category_name) {
                $compares['income'][$category_id][$dates[$j]] = [
                    'category_id' => $category_id,
                    'name' => $category_name,
                    'amount' => 0,
                    'currency_code' => setting('general.default_currency'),
                    'currency_rate' => 1
                ];
            }

            foreach ($expense_categories as $category_id => $category_name) {
                $compares['expense'][$category_id][$dates[$j]] = [
                    'category_id' => $category_id,
                    'name' => $category_name,
                    'amount' => 0,
                    'currency_code' => setting('general.default_currency'),
                    'currency_rate' => 1
                ];
            }

            $j += 2;
        }

        $totals['total'] = [
            'amount' => 0,
            'currency_code' => setting('general.default_currency'),
            'currency_rate' => 1
        ];

        $gross['income'] = $gross['expense'] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 'total' => 0];

        foreach ($income_categories as $category_id => $category_name) {
            $compares['income'][$category_id]['total'] = [
                'category_id' => $category_id,
                'name' => trans_choice('general.totals', 1),
                'amount' => 0,
                'currency_code' => setting('general.default_currency'),
                'currency_rate' => 1
            ];
        }

        foreach ($expense_categories as $category_id => $category_name) {
            $compares['expense'][$category_id]['total'] = [
                'category_id' => $category_id,
                'name' => trans_choice('general.totals', 1),
                'amount' => 0,
                'currency_code' => setting('general.default_currency'),
                'currency_rate' => 1
            ];
        }

        // Revenues
        if ($status != 'upcoming') {
            $revenues = Revenue::monthsOfYear('paid_at')->isNotTransfer()->get();
            $this->setAmount($totals, $compares, $revenues, 'revenue', 'paid_at');
        }

        // Payments
        if ($status != 'upcoming') {
            $payments = Payment::monthsOfYear('paid_at')->isNotTransfer()->get();
            $this->setAmount($totals, $compares, $payments, 'payment', 'paid_at');
        }

        $statuses = collect([
            'all' => trans('general.all'),
            'paid' => trans('invoices.paid'),
            'upcoming' => trans('general.upcoming'),
        ]);

        // Check if it's a print or normal request
        if (request('print')) {
            $view_template = 'reports.profit_loss.print';
        } else {
            $view_template = 'reports.profit_loss.index';
        }

        return view($view_template, compact('dates', 'income_categories', 'expense_categories', 'compares', 'totals', 'gross', 'statuses'));
    }

    private function setAmount(&$totals, &$compares, $items, $type, $date_field)
    {
        foreach ($items as $item) {
            $date = Date::parse($item->$date_field)->quarter;

            $group = ($type == 'revenue') ? 'income' : 'expense';

            if (!isset($compares[$group][$item->category_id])) {
                continue;
            }

            $amount = $item->amount;

            $compares[$group][$item->category_id][$date]['amount'] += $amount;
            $compares[$group][$item->category_id][$date]['currency_code'] = $item->currency_code;
            $compares[$group][$item->category_id][$date]['currency_rate'] = $item->currency_rate;
            $compares[$group][$item->category_id]['total']['amount'] += $amount;

            if ($group == 'income') {
                $totals[$date]['amount'] += $amount;
                $totals['total']['amount'] += $amount;
            } else {
                $totals[$date]['amount'] -= $amount;
                $totals['total']['amount'] -= $amount;
            }
        }
    }
}
