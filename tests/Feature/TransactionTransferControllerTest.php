<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;

test('existing transfer record can be deleted along with payment and revenue', closure: function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $from_account = Account::factory()->state(['company_id' => $company->id])->create();
    $to_account = Account::factory()->state(['company_id' => $company->id])->create();

    $transfer_category = Category::factory()->state([
        'company_id' => $company->id,
        'type'       => 'other',
        'name'       => 'Transfer',
    ])->create();

    $payment = Payment::factory()->state([
        'company_id'    => $company->id,
        'account_id'    => $from_account->id,
        'paid_at'       => Carbon::today(),
        'amount'        => '250.00',
        'category_id'   => $transfer_category->id,
        'vendor_id'     => 0,
        'currency_code' => 'USD',
    ])->create();

    $revenue = Revenue::factory()->state([
        'company_id'    => $company->id,
        'account_id'    => $to_account->id,
        'paid_at'       => Carbon::today(),
        'amount'        => '250.00',
        'category_id'   => $transfer_category->id,
        'customer_id'   => 0,
        'currency_code' => 'USD',
    ])->create();

    $transfer = Transfer::create([
        'company_id' => $company->id,
        'payment_id' => $payment->id,
        'revenue_id' => $revenue->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete('/transactions/transfers/' . $transfer->id);

    $response->assertRedirect('/transactions?account_id=' . $from_account->id);

    $this->assertSoftDeleted('transfers', ['id' => $transfer->id]);
    $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    $this->assertSoftDeleted('revenues', ['id' => $revenue->id]);
});

test('transfer deletion is rolled back if an error occurs during deletion', closure: function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $from_account = Account::factory()->state(['company_id' => $company->id])->create();
    $to_account = Account::factory()->state(['company_id' => $company->id])->create();

    $transfer_category = Category::factory()->state([
        'company_id' => $company->id,
        'type'       => 'other',
        'name'       => 'Transfer',
    ])->create();

    $payment = Payment::factory()->state([
        'company_id'    => $company->id,
        'account_id'    => $from_account->id,
        'paid_at'       => Carbon::today(),
        'amount'        => '250.00',
        'category_id'   => $transfer_category->id,
        'vendor_id'     => 0,
        'currency_code' => 'USD',
    ])->create();

    $revenue = Revenue::factory()->state([
        'company_id'    => $company->id,
        'account_id'    => $to_account->id,
        'paid_at'       => Carbon::today(),
        'amount'        => '250.00',
        'category_id'   => $transfer_category->id,
        'customer_id'   => 0,
        'currency_code' => 'USD',
    ])->create();

    $transfer = Transfer::create([
        'company_id' => $company->id,
        'payment_id' => $payment->id,
        'revenue_id' => $revenue->id,
    ]);

    Transfer::deleting(function () {
        throw new \Exception('Simulated deletion failure');
    });

    try {
        $this
            ->actingAs($user)
            ->withSession(['company_id' => $company->id])
            ->delete('/transactions/transfers/' . $transfer->id);
    } catch (\Throwable $e) {
        // Exception expected
    }

    $this->assertDatabaseHas('transfers', ['id' => $transfer->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('payments', ['id' => $payment->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('revenues', ['id' => $revenue->id, 'deleted_at' => null]);
});
