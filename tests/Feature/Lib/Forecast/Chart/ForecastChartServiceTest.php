<?php

use App\Lib\Forecast\Chart\ForecastChartService;
use App\Models\Account;
use App\Models\Category;
use App\Models\Payable;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;

test('full test', function () {
    $start_period = Carbon::createFromDate(2025, 5, 5);
    $end_period = Carbon::createFromDate(2025, 5, 15);
    $payment_category = Category::factory()->state(['type' => 'expense', 'name' => 'Sample Payment category'])->create();
    $revenue_category = Category::factory()->state(['type' => 'income', 'name' => 'Sample Revenue category'])->create();

    // todo: create recurring record

    $account = Account::factory()
        ->has(
            Receivable::factory()
                ->count(4)
                ->state(new Sequence(
                    ['due_at' => $start_period->clone()->subDays(1), 'amount' => '2000.00', 'category_id' => $revenue_category->id], // before start period: should be considered initial balance
                    ['due_at' => $start_period->clone()->addDays(1), 'amount' => '500.00', 'category_id' => $revenue_category->id],
                    ['due_at' => $start_period->clone()->addDays(3), 'amount' => '50.00', 'category_id' => $revenue_category->id],
                    ['due_at' => $end_period->clone()->addDays(1), 'amount' => '1000.00', 'category_id' => $revenue_category->id], // after end period: should be ignored
                ))
        )
        ->has(
            Payable::factory()
                ->count(4)
                ->state(new Sequence(
                    ['due_at' => $start_period->clone()->subDays(1), 'amount' => '1000.00', 'category_id' => $payment_category->id], // before start period: should be considered initial balance
                    ['due_at' => $start_period->clone()->addDays(1), 'amount' => '250.00', 'category_id' => $payment_category->id],
                    ['due_at' => $start_period->clone()->addDays(2), 'amount' => '500.00', 'category_id' => $payment_category->id],
                    ['due_at' => $end_period->clone()->addDays(1), 'amount' => '1000.00', 'category_id' => $payment_category->id], // after end period: should be ignored
                ))
        )
        ->create();

    $service = new ForecastChartService();
    $actual = $service->getDataOnPeriod($start_period, $end_period);
    $this->assertCount(11, $actual);
    $this->assertEquals('2025-05-05', $actual[0]->date);
    $this->assertEquals('1000', $actual[0]->balance); // 0 + (2000 - 1000) = 1000 // initial balance + (receivable - payable)
    $this->assertEquals('2025-05-06', $actual[1]->date);
    $this->assertEquals('1250', $actual[1]->balance); // 1000 + (500 - 250) = 1250 // previous balance + (receivable - payable)
    $this->assertEquals('2025-05-07', $actual[2]->date);
    $this->assertEquals('750', $actual[2]->balance); // 1250 + (0 - 500) = 750 // previous balance + (receivable - payable)
    $this->assertEquals('2025-05-08', $actual[3]->date);
    $this->assertEquals('800', $actual[3]->balance); // 750 + (50 - 0) = 800 // previous balance + (receivable - payable)
    $this->assertEquals('2025-05-09', $actual[4]->date);
    $this->assertEquals('800', $actual[4]->balance); // 800 + (0 - 0) = 800 // previous balance + (receivable - payable)
    $this->assertEquals('2025-05-15', $actual[10]->date);
    $this->assertEquals('800', $actual[10]->balance); // 800 + (0 - 0) = 800 // previous balance + (receivable - payable)
});
