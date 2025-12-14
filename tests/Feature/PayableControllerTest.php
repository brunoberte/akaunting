<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Company;
use App\Models\Payable;
use App\Models\User;
use App\Models\Vendor;

test('Valid record should be created', closure: function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $account = Account::factory()->state(['company_id' => $company->id])->create();
    $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'expense', 'name' => 'Sample Revenue category'])->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->post('/payables', [
            'account_id'          => $account->id,
            'amount'              => "12.44",
            'category_id'         => $category->id,
            'currency_code'       => "BRL",
            'due_at'              => "2025-01-01",
            'notes'               => "",
            'recurring_frequency' => "monthly",
            'title'               => "!teste",
            'vendor_id'           => $vendor->id,
        ]);
    $response->assertRedirect('/payables');
    $payable = Payable::query()->orderBy('id', 'desc')->first();
    $recurring = $payable->recurring;
    $this->assertEquals('12.44', $payable->amount);
    $this->assertEquals('2025-01-01', $payable->due_at->format('Y-m-d'));
    $this->assertEquals(Payable::class, $recurring->recurable_type);
    $this->assertEquals('monthly', $recurring->frequency);
    $this->assertEquals('1', $recurring->interval);
});
