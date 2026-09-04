<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Receivable;
use App\Models\Revenue;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('Customer list can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Customer::factory()->count(3)->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/customers');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/index')
        ->has('customers.data', 3)
    );
});

test('Customer list can be filtered by text', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Customer::factory()->state(['company_id' => $company->id, 'name' => 'Target Customer'])->create();
    Customer::factory()->state(['company_id' => $company->id, 'name' => 'Other Customer'])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/customers?filter_text=Target');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/index')
        ->has('customers.data', 1)
        ->where('customers.data.0.name', 'Target Customer')
    );
});

test('Customer create form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/customers/new');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/form')
        ->has('customer')
        ->has('currencies')
    );
});

test('Customer edit form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get("/customers/{$customer->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/form')
        ->where('customer.id', $customer->id)
        ->has('currencies')
    );
});

test('Customer can be created', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->post('/customers', [
            'name'          => 'ACME Corp',
            'email'         => 'contact@acme.com',
            'phone'         => '123456789',
            'tax_number'    => '987654321',
            'currency_code' => 'BRL',
            'website'       => 'https://acme.com',
            'address'       => '123 Main St',
            'reference'     => 'REF123',
            'enabled'       => true,
        ]);

    $response->assertRedirect('/customers');
    $this->assertDatabaseHas('customers', [
        'company_id' => $company->id,
        'name'       => 'ACME Corp',
        'email'      => 'contact@acme.com',
    ]);
});

test('Customer can be updated', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->patch("/customers/{$customer->id}", [
            'id'            => $customer->id,
            'company_id'    => $company->id,
            'name'          => 'Updated Name',
            'email'         => 'updated@acme.com',
            'phone'         => '999999999',
            'tax_number'    => '111111111',
            'currency_code' => 'USD',
            'website'       => 'https://updated.com',
            'address'       => '456 Other St',
            'reference'     => 'REF999',
            'enabled'       => false,
        ]);

    $response->assertRedirect('/customers');
    $this->assertDatabaseHas('customers', [
        'id'      => $customer->id,
        'name'    => 'Updated Name',
        'email'   => 'updated@acme.com',
        'enabled' => false,
    ]);
});

test('Customer status can be toggled', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $customer = Customer::factory()->state(['company_id' => $company->id, 'enabled' => true])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->from('/customers')
        ->patch("/customers/{$customer->id}/toggle-status");

    $response->assertRedirect('/customers');
    $this->assertDatabaseHas('customers', [
        'id'      => $customer->id,
        'enabled' => false,
    ]);

    $response2 = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->from('/customers')
        ->patch("/customers/{$customer->id}/toggle-status");

    $response2->assertRedirect('/customers');
    $this->assertDatabaseHas('customers', [
        'id'      => $customer->id,
        'enabled' => true,
    ]);
});

test('Customer can be deleted if not in use', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/customers/{$customer->id}");

    $response->assertRedirect('/customers');
    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});

test('Customer cannot be deleted if has revenues', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();

    Revenue::factory()->state([
        'company_id'  => $company->id,
        'customer_id' => $customer->id,
    ])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/customers/{$customer->id}");

    $response->assertSessionHasErrors(['customer']);
    $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
});

test('Customer cannot be deleted if has receivables', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $customer = Customer::factory()->state(['company_id' => $company->id])->create();

    Receivable::factory()->state([
        'company_id'  => $company->id,
        'customer_id' => $customer->id,
    ])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/customers/{$customer->id}");

    $response->assertSessionHasErrors(['customer']);
    $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
});
