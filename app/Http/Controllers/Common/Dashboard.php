<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Banking\Account;
use App\Models\Expense\Payable;
use App\Models\Expense\Payment;
use App\Models\Income\Receivable;
use App\Models\Income\Revenue;
use App\Models\Setting\Category;
use App\Util;
use Carbon\Carbon;
use Charts;
use Date;
use Illuminate\Support\Facades\Response;

class Dashboard extends Controller
{
	/** @var Date */
    public $today;

    public $income_donut = ['colors' => [], 'labels' => [], 'values' => []];

    public $expense_donut = ['colors' => [], 'labels' => [], 'values' => []];

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->today = Date::today();

        list($total_incomes, $total_expenses, $total_profit) = $this->getTotals();

        $cashflow = $this->getCashFlow();

        $current_balance = $this->getCurrentBalance();
        $forecast_table = $this->getForecastTable();
        $forecast_chart = $this->getForecastChart($forecast_table, $current_balance);

        list($donut_incomes, $donut_expenses) = $this->getDonuts();

        $accounts = Account::enabled()->orderBy('name')->get();

        return view('common.dashboard.index', compact(
            'total_incomes',
            'total_expenses',
            'total_profit',
            'cashflow',
            'current_balance',
            'forecast_table',
            'forecast_chart',
            'donut_incomes',
            'donut_expenses',
            'accounts'
        ));
    }

    public function cashFlow()
    {
        $this->today = Date::today();

        $content = $this->getCashFlow()->render();

        //return response()->setContent($content)->send();

        echo $content;
    }

    private function getTotals()
    {
        list($incomes_amount, $expenses_amount) = $this->calculateAmounts();

        $incomes_progress = 100;

        // Totals
        $total_incomes = array(
            'total'             => $incomes_amount,
            'progress'          => $incomes_progress
        );

        $expenses_progress = 100;

        $total_expenses = array(
            'total'         => $expenses_amount,
            'progress'      => $expenses_progress
        );

        $amount_profit = $incomes_amount - $expenses_amount;
        $open_profit = 0;
        $overdue_profit = 0;

        $total_progress = 100;

        if (!empty($open_profit) && !empty($overdue_profit)) {
            $total_progress = (int) ($open_profit * 100) / ($open_profit + $overdue_profit);
        }

        $total_profit = array(
            'total'         => $amount_profit,
            'open'          => Util::money($open_profit, setting('general.default_currency'), true),
            'overdue'       => Util::money($overdue_profit, setting('general.default_currency'), true),
            'progress'      => $total_progress
        );

        return array($total_incomes, $total_expenses, $total_profit);
    }

    private function getCashFlow()
    {
        $start = Date::parse(request('start', $this->today->copy()->startOfYear()->format('Y-m-d')));
        $end = Date::parse(request('end', $this->today->copy()->format('Y-m-d')));
        $period = request('period', 'day');

        $labels = array();

        $s = clone $start;

        while ($s <= $end) {

            switch ($period) {
                case 'day':
                    $labels[] = $s->format('d M Y');
                    $s->addDay();
                    break;
                case 'month':
                    $labels[] = $s->format('M Y');
                    $s->addMonth();
                    break;
                default:
                    $labels[] = $s->format('M Y');
                    $s->addMonths(3);
            }
        }

        $income = $this->calculateCashFlowTotals('income', $start, $end, $period);
        $expense = $this->calculateCashFlowTotals('expense', $start, $end, $period);

        $profit = $this->calculateCashFlowProfit($income, $expense);

        $chart = Charts::multi('line', 'chartjs')
            ->dimensions(0, 300)
            ->colors(['#6da252', '#00c0ef', '#F56954'])
            ->dataset(trans_choice('general.profits', 1), $profit)
            ->dataset(trans_choice('general.incomes', 1), $income)
            ->dataset(trans_choice('general.expenses', 1), $expense)
            ->labels($labels)
            ->credits(false)
            ->view('vendor.consoletvs.charts.chartjs.multi.line');

        return $chart;
    }

    private function getCurrentBalance()
    {
        //TODO: cache

        // get opening balance of enabled accounts
        $current_balance = Account::query()
            ->enabled()
            ->sum('opening_balance');

        // Sum revenues
        $current_balance += Revenue::query()
            ->sum('amount');

        // Subtract payments
        $current_balance -= Payment::query()
            ->sum('amount');

        return $current_balance;
    }

    private function getForecastTable()
    {
        //TODO: cache

        $end = Carbon::today()->endOfDay()->addDays(90);

        $forecast_table = [];

        $receivables = Receivable::query()
            ->with(['recurring'])
            ->get();
        $this->_addItemsToForecastTable($receivables, $end, $forecast_table);


        $payables = Payable::query()
            ->with(['recurring'])
            ->get();
        $this->_addItemsToForecastTable($payables, $end, $forecast_table);

        ksort($forecast_table);

        return $forecast_table;
    }

    private function _addItemsToForecastTable($list, Carbon $date_limit, &$forecast_table)
    {
        foreach($list as $item) {
            if ($item->due_at <= $date_limit) {
                $dt_formatted = $item->due_at->format('Y-m-d');
                if (!isset($forecast_table[$dt_formatted])) {
                    $forecast_table[$dt_formatted] = [];
                }
                $forecast_table[$dt_formatted][] = [
                    'type' => get_class($item),
                    'id' => $item->id,
                    'title' => $item->title,
                    'amount' => $item->amount,
                    'currency_code' => $item->currency_code,
                ];

                //check recurring
                if ($item->recurring) {
                    $next_date = $item->recurring->getNextDate();
                    while($next_date !== false && $next_date < $date_limit) {
                        $dt_formatted = $next_date->format('Y-m-d');
                        if (!isset($forecast_table[$dt_formatted])) {
                            $forecast_table[$dt_formatted] = [];
                        }
                        $forecast_table[$dt_formatted][] = [
                            'type' => get_class($item),
                            'id' => $item->id,
                            'title' => $item->title,
                            'amount' => $item->amount,
                            'currency_code' => $item->currency_code,
                        ];
                        $next_date = $item->recurring->getNextDate();
                    }
                }
            }
        }
    }

    private function getForecastChart(array $forecast_table, $initial_balance)
    {
        //TODO: cache


        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay()->addDays(90);

        $labels = [];
        $values = [];

        $receivables = Receivable::query()
            ->with(['recurring'])
            ->get();
        /** @var Receivable $receivable */
        foreach($receivables as $receivable) {
            if ($receivable->due_at <= $end) {
                $dt_formatted = $receivable->due_at->format('Y-m-d');
                if ($receivable->due_at < $start) {
                    $dt_formatted = $start->format('Y-m-d');
                }
                if (!isset($values[$dt_formatted])) {
                    $values[$dt_formatted] = 0;
                }
                $values[$dt_formatted] += $receivable->amount;

                //TODO: check recurring
                if ($receivable->recurring) {
                    $next_date = $receivable->recurring->getNextDate();
                    while($next_date !== false && $next_date < $end) {
                        $dt_formatted = $next_date->format('Y-m-d');
                        if ($next_date < $start) {
                            $dt_formatted = $start->format('Y-m-d');
                        }
                        if (!isset($values[$dt_formatted])) {
                            $values[$dt_formatted] = 0;
                        }
                        $values[$dt_formatted] += $receivable->amount;
                        $next_date = $receivable->recurring->getNextDate();
                    }
                }
            }
        }

        $payables = Payable::query()
            ->with(['recurring'])
            ->get();
        /** @var Payable $receivable */
        foreach($payables as $payable) {
            if ($payable->due_at <= $end) {
                $dt_formatted = $payable->due_at->format('Y-m-d');
                if ($payable->due_at < $start) {
                    $dt_formatted = $start->format('Y-m-d');
                }
                if (!isset($values[$dt_formatted])) {
                    $values[$dt_formatted] = 0;
                }
                $values[$dt_formatted] -= $payable->amount;

                //TODO: check recurring
                if ($payable->recurring) {
                    $next_date = $payable->recurring->getNextDate();
                    while($next_date !== false && $next_date < $end) {
                        $dt_formatted = $next_date->format('Y-m-d');
                        if ($next_date < $start) {
                            $dt_formatted = $start->format('Y-m-d');
                        }
                        if (!isset($values[$dt_formatted])) {
                            $values[$dt_formatted] = 0;
                        }
                        $values[$dt_formatted] -= $payable->amount;
                        $next_date = $payable->recurring->getNextDate();
                    }
                }
            }
        }

        // apply initial balance
        $current_balance = $initial_balance;
        $s = clone $start;
        while($s <= $end) {
            $dt_formatted = $s->format('Y-m-d');
            $labels[] = $dt_formatted;
            if (isset($values[$dt_formatted])) {
                $current_balance += $values[$dt_formatted];
            }
            $values[$dt_formatted] = $current_balance;
            $s->addDay();
        }

        ksort($values);

        $chart = Charts::multi('line', 'chartjs')
            ->dimensions(0, 300)
            ->colors(['#6da252'])
            ->dataset(trans_choice('general.balance', 1), $values)
            ->labels($labels)
            ->credits(false)
            ->view('vendor.consoletvs.charts.chartjs.multi.line');

        return $chart;
    }

    private function getDonuts()
    {
        // Show donut prorated if there is no income
        if (array_sum($this->income_donut['values']) == 0) {
            foreach ($this->income_donut['values'] as $key => $value) {
                $this->income_donut['values'][$key] = 1;
            }
        }

        // Get 6 categories by amount
        $colors = $labels = [];
        $values = collect($this->income_donut['values'])->sort()->reverse()->take(6)->all();
        foreach ($values as $id => $val) {
            $colors[$id] = $this->income_donut['colors'][$id];
            $labels[$id] = $this->income_donut['labels'][$id];
        }

        $donut_incomes = Charts::create('donut', 'chartjs')
            ->colors($colors)
            ->labels($labels)
            ->values($values)
            ->dimensions(0, 160)
            ->credits(false)
            ->view('vendor.consoletvs.charts.chartjs.donut');

        // Show donut prorated if there is no expense
        if (array_sum($this->expense_donut['values']) == 0) {
            foreach ($this->expense_donut['values'] as $key => $value) {
                $this->expense_donut['values'][$key] = 1;
            }
        }

        // Get 6 categories by amount
        $colors = $labels = [];
        $values = collect($this->expense_donut['values'])->sort()->reverse()->take(6)->all();
        foreach ($values as $id => $val) {
            $colors[$id] = $this->expense_donut['colors'][$id];
            $labels[$id] = $this->expense_donut['labels'][$id];
        }

        $donut_expenses = Charts::create('donut', 'chartjs')
            ->colors($colors)
            ->labels($labels)
            ->values($values)
            ->dimensions(0, 160)
            ->credits(false)
            ->view('vendor.consoletvs.charts.chartjs.donut');

        return array($donut_incomes, $donut_expenses);
    }

    private function calculateAmounts()
    {
        $incomes_amount = 0;
        $expenses_amount = 0;

        // Get categories
        $categories = Category::with(['payments', 'revenues'])
            ->orWhere('type', 'income')
            ->orWhere('type', 'expense')
            ->enabled()
            ->get();

        foreach ($categories as $category) {
            switch ($category->type) {
                case 'income':
                    $amount = 0;

                    // Revenues
                    foreach ($category->revenues as $revenue) {
                        $amount += $revenue->amount;
                    }

                    $incomes_amount += $amount;

                    $this->addToIncomeDonut($category->color, $amount, $category->name);

                    break;
                case 'expense':
                    $amount = 0;

                    // Payments
                    foreach ($category->payments as $payment) {
                        $amount += $payment->amount;
                    }

                    $expenses_amount += $amount;

                    $this->addToExpenseDonut($category->color, $amount, $category->name);

                    break;
            }
        }

        return array($incomes_amount, $expenses_amount);
    }

    private function calculateCashFlowTotals($type, $start, $end, $period)
    {
        $totals = array();

        if ($type == 'income') {
            $m1 = '\App\Models\Income\Revenue';
        } else {
            $m1 = '\App\Models\Expense\Payment';
        }

        switch ($period) {
            case 'day':
                $date_format = 'Y-m-d';
                $n = 1;
                $start_date = $start->format($date_format);
                $end_date = $end->format($date_format);
                $next_date = $start_date;
                break;
            case 'month':
                $date_format = 'Y-m';
                $n = 1;
                $start_date = $start->format($date_format);
                $end_date = $end->format($date_format);
                $next_date = $start_date;
                break;
            default:
                $n = 3;
                $start_date = $start->quarter;
                $end_date = $end->quarter;
                $next_date = $start_date;
                break;
        }

        $s = clone $start;

        //$totals[$start_date] = 0;
        while ($next_date <= $end_date) {
            $totals[$next_date] = 0;

            switch ($period) {
                case 'day':
                    $next_date = $s->addDay($n)->format($date_format);
                    break;
                case 'month':
                    $next_date = $s->addMonths($n)->format($date_format);
                    break;
                default:
                    if (isset($totals[4])) {
                        break;
                    }

                    $next_date = $s->addMonths($n)->quarter;
                    break;
            }
        }

        $items_1 = $m1::whereBetween('paid_at', [$start, $end])->isNotTransfer()->get();

        $this->setCashFlowTotals($totals, $items_1, $date_format, $period);

//        $items_2 = $m2::whereBetween('paid_at', [$start, $end])->get();
//
//        $this->setCashFlowTotals($totals, $items_2, $date_format, $period);

        return $totals;
    }

    private function setCashFlowTotals(&$totals, $items, $date_format, $period)
    {
        foreach ($items as $item) {
            switch ($period) {
                case 'day':
                case 'month':
                    $i = Date::parse($item->paid_at)->format($date_format);
                    break;
                default:
                    $i = Date::parse($item->paid_at)->quarter;
                    break;
            }


            if (!isset($totals[$i])) {
                continue;
            }

            $totals[$i] += $item->amount;
        }
    }

    private function calculateCashFlowProfit($incomes, $expenses)
    {
        $profit = [];

        foreach ($incomes as $key => $income) {
            $profit[$key] = $income - $expenses[$key];
            /*
            if ($income > 0 && $income > $expenses[$key]) {
                $profit[$key] = $income - $expenses[$key];
            } else {
                $profit[$key] = 0;
            }
            */
        }

        return $profit;
    }

    private function addToIncomeDonut($color, $amount, $text)
    {
        $this->income_donut['colors'][] = $color;
        $this->income_donut['labels'][] = Util::money($amount, setting('general.default_currency'), true) . ' - ' . $text;
        $this->income_donut['values'][] = (int) $amount;
    }

    private function addToExpenseDonut($color, $amount, $text)
    {
        $this->expense_donut['colors'][] = $color;
        $this->expense_donut['labels'][] = Util::money($amount, setting('general.default_currency'), true) . ' - ' . $text;
        $this->expense_donut['values'][] = (int) $amount;
    }
}
