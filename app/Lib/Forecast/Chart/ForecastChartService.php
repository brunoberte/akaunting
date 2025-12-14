<?php

namespace App\Lib\Forecast\Chart;

use App\Models\Account;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\Receivable;
use App\Models\Revenue;
use Carbon\Carbon;

class ForecastChartService
{
    /** @return ForecastChartDTO[] */
    public function getDataOnPeriod(string $currency_code, Carbon $start_date, Carbon $end_date): array
    {
        // TODO: custom parameters
        $initial_balance = $this->getCurrentBalance();
        $start_date->startOfDay();
        $end_date->endOfDay();

        /** @var ForecastChartDTO[] $values */
        $values = [];

        $receivables = Receivable::query()
            ->with(['recurring'])
            ->where('currency_code', $currency_code)
            ->get();
        /** @var Receivable $receivable */
        foreach ($receivables as $receivable) {
            if ($receivable->due_at <= $end_date) {
                $dt_formatted = $receivable->due_at->format('Y-m-d');
                if ($receivable->due_at < $start_date) { // atrasado
                    $dt_formatted = $start_date->format('Y-m-d');
                }
                if (!isset($values[$dt_formatted])) {
                    $values[$dt_formatted] = new ForecastChartDTO($dt_formatted, 0);
                }
                $values[$dt_formatted]->incrementBalance($receivable->amount);

                //TODO: check recurring
                if ($receivable->recurring) {
                    $next_date = $receivable->recurring->getNextDate();
                    while ($next_date !== false && $next_date < $end_date) {
                        $dt_formatted = $next_date->format('Y-m-d');
                        if ($next_date < $start_date) {
                            $dt_formatted = $start_date->format('Y-m-d');
                        }
                        if (!isset($values[$dt_formatted])) {
                            $values[$dt_formatted] = new ForecastChartDTO($dt_formatted, 0);
                        }
                        $values[$dt_formatted]->incrementBalance($receivable->amount);
                        $next_date = $receivable->recurring->getNextDate();
                    }
                }
            }
        }

        $payables = Payable::query()
            ->with(['recurring'])
            ->where('currency_code', $currency_code)
            ->get();
        /** @var Payable $receivable */
        foreach ($payables as $payable) {
            if ($payable->due_at <= $end_date) {
                $dt_formatted = $payable->due_at->format('Y-m-d');
                if ($payable->due_at < $start_date) {
                    $dt_formatted = $start_date->format('Y-m-d');
                }
                if (!isset($values[$dt_formatted])) {
                    $values[$dt_formatted] = new ForecastChartDTO($dt_formatted, 0);
                }
                $values[$dt_formatted]->decrementBalance($payable->amount);

                //TODO: check recurring
                if ($payable->recurring) {
                    $next_date = $payable->recurring->getNextDate();
                    while ($next_date !== false && $next_date < $end_date) {
                        $dt_formatted = $next_date->format('Y-m-d');
                        if ($next_date < $start_date) {
                            $dt_formatted = $start_date->format('Y-m-d');
                        }
                        if (!isset($values[$dt_formatted])) {
                            $values[$dt_formatted] = new ForecastChartDTO($dt_formatted, 0);
                        }
                        $values[$dt_formatted]->decrementBalance($payable->amount);
                        $next_date = $payable->recurring->getNextDate();
                    }
                }
            }
        }

        // apply initial balance
        $current_balance = $initial_balance;
        $s = clone $start_date;
        while ($s <= $end_date) {
            $dt_formatted = $s->format('Y-m-d');
            if (isset($values[$dt_formatted])) {
                $current_balance += $values[$dt_formatted]->balance;
            }
            $values[$dt_formatted] = new ForecastChartDTO($dt_formatted, $current_balance);
            $s->addDay();
        }

        ksort($values);
        return array_values($values);
    }

    private function getCurrentBalance(): float
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
}
