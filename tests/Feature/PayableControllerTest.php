<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Company;
use App\Models\Payable;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('Payable list can be filtered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Payable::factory()->state(['company_id' => $company->id, 'title' => 'Target Payable'])->create();
    Payable::factory()->state(['company_id' => $company->id, 'title' => 'Other'])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/payables?filter_text=Target');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('payables/index')
        ->has('record_list', 1)
        ->where('record_list.0.title', 'Target Payable')
    );
});

test('Payable list can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Payable::factory()->count(3)->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/payables');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('payables/index')
        ->has('record_list', 3)
    );
});

test('Payable create form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/payables/new');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('payables/form')
        ->has('payable')
    );
});

test('Payable edit form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $payable = Payable::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get("/payables/{$payable->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('payables/form')
        ->where('payable.id', $payable->id)
    );
});

test('Payable can be updated', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $account = Account::factory()->state(['company_id' => $company->id])->create();
    $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'expense'])->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();
    $payable = Payable::factory()->state([
        'company_id'  => $company->id,
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'vendor_id'   => $vendor->id,
    ])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->patch("/payables/{$payable->id}", [
            'id'                  => $payable->id,
            'company_id'          => $company->id,
            'account_id'          => $account->id,
            'amount'              => "100.00",
            'category_id'         => $category->id,
            'currency_code'       => "BRL",
            'due_at'              => "2025-02-01",
            'notes'               => "Updated notes",
            'recurring_frequency' => "no",
            'title'               => "Updated title",
            'vendor_id'           => $vendor->id,
        ]);

    $response->assertRedirect('/payables');
    $this->assertDatabaseHas('payables', [
        'id'     => $payable->id,
        'title'  => 'Updated title',
        'amount' => 100.00
    ]);
});

test('Payable next occurrence can be skipped', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $payable = Payable::factory()->state([
        'company_id' => $company->id,
        'due_at'     => '2025-01-01'
    ])->create();

    $payable->recurring()->create([
        'company_id' => $company->id,
        'frequency'  => 'monthly',
        'interval'   => 1,
        'started_at' => '2025-01-01',
        'count'      => 5,
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->post("/payables/{$payable->id}/skip-next");

    $response->assertRedirect('/payables');
    $payable->refresh();
    $this->assertEquals('2025-02-01', $payable->due_at->format('Y-m-d'));
    $this->assertEquals(4, $payable->recurring->count);
});

test('Payable can be deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $payable = Payable::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/payables/{$payable->id}");

    $response->assertRedirect('/payables');
    $this->assertSoftDeleted('payables', ['id' => $payable->id]);
});

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
