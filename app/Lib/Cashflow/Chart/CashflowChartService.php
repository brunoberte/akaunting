<?php

namespace App\Lib\Cashflow\Chart;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Revenue;
use Carbon\Carbon;

class CashflowChartService
{
    /** @return CashflowChartDTO[] */
    public function getDataOnPeriod(string $currency_code, Carbon $start_date, Carbon $end_date): array
    {
        $start_date->startOfDay();
        $end_date->endOfDay();

        $category_id = Category::transfer();

        /** @var CashflowChartDTO[] $values */
        $values = [];

        /** @var Revenue[] $revenues */
        $revenues = Revenue::query()
            ->where('paid_at', '>=', $start_date)
            ->where('paid_at', '<=', $end_date)
            ->where('category_id', '!=', $category_id)
            ->where('currency_code', $currency_code)
            ->get();
        foreach ($revenues as $revenue) {
            if ($revenue->paid_at <= $end_date) {
                $dt_formatted = $revenue->paid_at->format('Y-m-d');
                if (!isset($values[$dt_formatted])) {
                    $values[$dt_formatted] = new CashflowChartDTO($dt_formatted);
                }
                $values[$dt_formatted]->incrementIncome($revenue->amount);
            }
        }

        /** @var Payment[] $payments */
        $payments = Payment::query()
            ->where('paid_at', '>=', $start_date)
            ->where('paid_at', '<=', $end_date)
            ->where('category_id', '!=', $category_id)
            ->where('currency_code', $currency_code)
            ->get();
        foreach ($payments as $payment) {
            $dt_formatted = $payment->paid_at->format('Y-m-d');
            if (!isset($values[$dt_formatted])) {
                $values[$dt_formatted] = new CashflowChartDTO($dt_formatted);
            }
            $values[$dt_formatted]->incrementExpense($payment->amount);
        }

        // apply initial balance
        $current_balance = $this->getBalanceOnDate($currency_code, $start_date->subDay()->clone());
        $s = clone $start_date;
        while ($s <= $end_date) {
            $dt_formatted = $s->format('Y-m-d');
            if (!isset($values[$dt_formatted])) {
                $values[$dt_formatted] = new CashflowChartDTO($dt_formatted);
            }
            $current_balance += $values[$dt_formatted]->income + $values[$dt_formatted]->expense;
            $values[$dt_formatted]->setBalance($current_balance);
            $s->addDay();
        }

        ksort($values);
        return array_values($values);
    }

    private function getBalanceOnDate(string $currency_code, Carbon $date): float
    {
        $total = 0;
        // Sum revenues
        $total += Revenue::query()
            ->where('currency_code', $currency_code)
            ->where('paid_at', '<=', $date->endOfDay()->format('Y-m-d H:i:s'))
            ->sum('amount');

        // Subtract payments
        $total -= Payment::query()
            ->where('currency_code', $currency_code)
            ->where('paid_at', '<=', $date->endOfDay()->format('Y-m-d H:i:s'))
            ->sum('amount');

        return $total;
    }
}

