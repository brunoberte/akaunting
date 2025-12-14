<?php

namespace App\Lib\Cashflow\Chart;

class CashflowChartDTO
{
    public float $income = 0;
    public float $expense = 0;
    public float $balance = 0;

    public function __construct(
        public readonly string $date,
    )
    {
    }

    public function incrementIncome(float $amount): void
    {
        $this->income = $this->income + $amount;
    }

    public function incrementExpense(float $amount): void
    {
        $this->expense = $this->expense - $amount;
    }

    public function setBalance(float $amount): void
    {
        $this->balance = $amount;
    }
}
