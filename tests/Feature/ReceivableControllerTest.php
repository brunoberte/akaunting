<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Company;
use App\Models\Receivable;
use App\Models\User;
use App\Models\Customer;
use Inertia\Testing\AssertableInertia as Assert;

test('Receivable list can be filtered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Receivable::factory()->state(['company_id' => $company->id, 'title' => 'Target Receivable'])->create();
    Receivable::factory()->state(['company_id' => $company->id, 'title' => 'Other'])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/receivables?filter_text=Target');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('receivables/index')
        ->has('record_list', 1)
        ->where('record_list.0.title', 'Target Receivable')
    );
});

test('Receivable list can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Receivable::factory()->count(3)->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/receivables');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('receivables/index')
        ->has('record_list', 3)
    );
});

test('Receivable create form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/receivables/new');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('receivables/form')
        ->has('receivable')
    );
});

test('Receivable edit form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $receivable = Receivable::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get("/receivables/{$receivable->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('receivables/form')
        ->where('receivable.id', $receivable->id)
    );
});

test('Receivable can be created', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $account = Account::factory()->state(['company_id' => $company->id])->create();
    $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'income'])->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->post('/receivables', [
            'company_id'          => $company->id,
            'account_id'          => $account->id,
            'amount'              => "150.00",
            'category_id'         => $category->id,
            'currency_code'       => "BRL",
            'due_at'              => "2025-01-15",
            'notes'               => "Sample notes",
            'recurring_frequency' => "monthly",
            'recurring_count'     => 12,
            'title'               => "New Receivable",
            'customer_id'         => $customer->id,
        ]);

    $response->assertRedirect('/receivables');
    $this->assertDatabaseHas('receivables', [
        'title'  => 'New Receivable',
        'amount' => 150.00
    ]);

    $receivable = Receivable::where('title', 'New Receivable')->first();
    $this->assertNotNull($receivable->recurring);
    $this->assertEquals('monthly', $receivable->recurring->frequency);
});

test('Receivable can be updated', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $account = Account::factory()->state(['company_id' => $company->id])->create();
    $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'income'])->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();
    $receivable = Receivable::factory()->state([
        'company_id'  => $company->id,
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'customer_id' => $customer->id,
    ])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->patch("/receivables/{$receivable->id}", [
            'id'                  => $receivable->id,
            'company_id'          => $company->id,
            'account_id'          => $account->id,
            'amount'              => "200.00",
            'category_id'         => $category->id,
            'currency_code'       => "BRL",
            'due_at'              => "2025-02-15",
            'notes'               => "Updated notes",
            'recurring_frequency' => "no",
            'title'               => "Updated Receivable",
            'customer_id'         => $customer->id,
        ]);

    $response->assertRedirect('/receivables');
    $this->assertDatabaseHas('receivables', [
        'id'     => $receivable->id,
        'title'  => 'Updated Receivable',
        'amount' => 200.00
    ]);
});

test('Receivable next occurrence can be skipped', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $receivable = Receivable::factory()->state([
        'company_id' => $company->id,
        'due_at'     => '2025-01-01'
    ])->create();

    $receivable->recurring()->create([
        'company_id' => $company->id,
        'frequency'  => 'weekly',
        'interval'   => 1,
        'started_at' => '2025-01-01',
        'count'      => 3,
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->post("/receivables/{$receivable->id}/skip-next");

    $response->assertRedirect('/receivables');
    $receivable->refresh();
    $this->assertEquals('2025-01-08', $receivable->due_at->format('Y-m-d'));
    $this->assertEquals(2, $receivable->recurring->count);
});

test('Receivable can be deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $receivable = Receivable::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/receivables/{$receivable->id}");

    $response->assertRedirect('/receivables');
    $this->assertSoftDeleted('receivables', ['id' => $receivable->id]);
});
