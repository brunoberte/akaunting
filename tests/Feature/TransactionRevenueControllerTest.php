<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Company;
use App\Models\Revenue;
use App\Models\User;
use Carbon\Carbon;

test('existing record can be deleted', closure: function () {
    $user = User::factory()->create();
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $account2 = Account::factory()->state(['company_id' => $company2->id])->create();

    $revenue_category_2 = Category::factory()->state(['company_id' => $company2->id, 'type' => 'income', 'name' => 'Sample Revenue category'])->create();

    $revenue2 = Revenue::factory()
        ->state([
            'company_id'  => $company2->id,
            'account_id'  => $account2->id,
            'paid_at'     => Carbon::today(),
            'amount'      => '500.00',
            'category_id' => $revenue_category_2->id
        ])
        ->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company2->id])
        ->delete('/transactions/revenues/' . $revenue2->id);
    $response->assertRedirect('/transactions?account_id=' . $account2->id);
});

// TODO: current user has access to record company?
