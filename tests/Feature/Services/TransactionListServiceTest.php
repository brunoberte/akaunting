<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Revenue;
use App\Services\TransactionListService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;

test('full test', function () {
    $start_period = Carbon::createFromDate(2025, 5, 5);
    $end_period = Carbon::createFromDate(2025, 5, 15);
    $payment_category = Category::factory()->state(['type' => 'expense', 'name' => 'Sample Payment category'])->create();
    $revenue_category = Category::factory()->state(['type' => 'income', 'name' => 'Sample Revenue category'])->create();
    $account = Account::factory()
        ->has(
            Payment::factory()
                ->count(4)
                ->state(new Sequence(
                    ['paid_at' => $start_period->clone()->subDays(1), 'amount' => '1000.00', 'category_id' => $payment_category->id], // before start period: for initial balance
                    ['paid_at' => $start_period->clone()->addDays(1), 'amount' => '250.00', 'category_id' => $payment_category->id],
                    ['paid_at' => $start_period->clone()->addDays(2), 'amount' => '250.00', 'category_id' => $payment_category->id],
                    ['paid_at' => $end_period->clone()->addDays(1), 'amount' => '1000.00', 'category_id' => $payment_category->id], // after end period: should be ignored
                ))
        )
        ->has(
            Revenue::factory()
                ->count(4)
                ->state(new Sequence(
                    ['paid_at' => $start_period->clone()->subDays(1), 'amount' => '2000.00', 'category_id' => $revenue_category->id], // before start period: for initial balance
                    ['paid_at' => $start_period->clone()->addDays(1), 'amount' => '500.00', 'category_id' => $revenue_category->id],
                    ['paid_at' => $start_period->clone()->addDays(2), 'amount' => '500.00', 'category_id' => $revenue_category->id],
                    ['paid_at' => $end_period->clone()->addDays(1), 'amount' => '1000.00', 'category_id' => $revenue_category->id], // after end period: should be ignored
                ))
        )
        ->create();

    $service = new TransactionListService();
    $actual = $service->getTransactionsForAccountOnPeriod($account, $start_period, $end_period);
    $this->assertCount(4, $actual);
    // TODO: assert initial balance and balance for each row
});
