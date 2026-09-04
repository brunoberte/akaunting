<?php

use App\Models\Company;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('Vendor list can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Vendor::factory()->count(3)->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/vendors');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('vendors/index')
        ->has('vendors.data', 3)
    );
});

test('Vendor list can be filtered by text', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    Vendor::factory()->state(['company_id' => $company->id, 'name' => 'Target Vendor'])->create();
    Vendor::factory()->state(['company_id' => $company->id, 'name' => 'Other Vendor'])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/vendors?filter_text=Target');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('vendors/index')
        ->has('vendors.data', 1)
        ->where('vendors.data.0.name', 'Target Vendor')
    );
});

test('Vendor create form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get('/vendors/new');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('vendors/form')
        ->has('vendor')
        ->has('currencies')
    );
});

test('Vendor edit form can be rendered', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->get("/vendors/{$vendor->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('vendors/form')
        ->where('vendor.id', $vendor->id)
        ->has('currencies')
    );
});

test('Vendor can be created', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->post('/vendors', [
            'name'          => 'Supplier Corp',
            'email'         => 'sales@supplier.com',
            'phone'         => '987654321',
            'tax_number'    => '123456789',
            'currency_code' => 'BRL',
            'website'       => 'https://supplier.com',
            'address'       => '456 Supply Way',
            'reference'     => 'SUP123',
            'enabled'       => true,
        ]);

    $response->assertRedirect('/vendors');
    $this->assertDatabaseHas('vendors', [
        'company_id' => $company->id,
        'name'       => 'Supplier Corp',
        'email'      => 'sales@supplier.com',
    ]);
});

test('Vendor can be updated', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->patch("/vendors/{$vendor->id}", [
            'id'            => $vendor->id,
            'company_id'    => $company->id,
            'name'          => 'Updated Supplier',
            'email'         => 'updated@supplier.com',
            'phone'         => '111222333',
            'tax_number'    => '444555666',
            'currency_code' => 'USD',
            'website'       => 'https://updated-supplier.com',
            'address'       => '789 Logistics Rd',
            'reference'     => 'SUP999',
            'enabled'       => false,
        ]);

    $response->assertRedirect('/vendors');
    $this->assertDatabaseHas('vendors', [
        'id'      => $vendor->id,
        'name'    => 'Updated Supplier',
        'email'   => 'updated@supplier.com',
        'enabled' => false,
    ]);
});

test('Vendor can be deleted if not in use', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/vendors/{$vendor->id}");

    $response->assertRedirect('/vendors');
    $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
});

test('Vendor cannot be deleted if has payments', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();

    Payment::factory()->state([
        'company_id' => $company->id,
        'vendor_id'  => $vendor->id,
    ])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/vendors/{$vendor->id}");

    $response->assertSessionHasErrors(['vendor']);
    $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'deleted_at' => null]);
});

test('Vendor cannot be deleted if has payables', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $vendor = Vendor::factory()->state(['company_id' => $company->id])->create();

    Payable::factory()->state([
        'company_id' => $company->id,
        'vendor_id'  => $vendor->id,
    ])->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['company_id' => $company->id])
        ->delete("/vendors/{$vendor->id}");

    $response->assertSessionHasErrors(['vendor']);
    $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'deleted_at' => null]);
});
