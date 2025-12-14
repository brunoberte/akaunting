<?php

namespace App\Lib\Forecast\Chart;

class ForecastChartDTO
{
    public function __construct(
        public readonly string $date,
        public float           $balance,
    )
    {
    }

    public function incrementBalance(float $amount): void
    {
        $this->balance = $this->balance + $amount;
    }

    public function decrementBalance(float $amount): void
    {
        $this->balance = $this->balance - $amount;
    }
}
