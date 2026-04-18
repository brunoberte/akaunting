<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Payable;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

test('Recurring trait can create and update recurring through Payable', function () {
    $company = Company::factory()->create();
    $payable = Payable::factory()->state(['company_id' => $company->id])->create();

    // Create
    $payable->createRecurring([
        'recurring_frequency'        => 'custom',
        'recurring_custom_frequency' => 'weekly',
        'recurring_interval'         => 2,
        'recurring_count'            => 10,
        'due_at'                     => '2025-01-01'
    ]);

    $this->assertDatabaseHas('recurring', [
        'recurable_id'   => $payable->id,
        'recurable_type' => Payable::class,
        'frequency'      => 'weekly',
        'interval'       => 2,
        'count'          => 10
    ]);

    // Update to no recurring
    $payable->updateRecurring(['recurring_frequency' => 'no']);
    $this->assertDatabaseMissing('recurring', [
        'recurable_id'   => $payable->id,
        'recurable_type' => Payable::class
    ]);

    // Re-create via update
    $payable->updateRecurring([
        'recurring_frequency' => 'monthly',
        'recurring_count'     => 5,
        'due_at'              => '2025-01-01'
    ]);
    $this->assertDatabaseHas('recurring', [
        'recurable_id'   => $payable->id,
        'recurable_type' => Payable::class,
        'frequency'      => 'monthly',
        'count'          => 5
    ]);
});

test('skipNext correctly advances date for different frequencies', function () {
    $company = Company::factory()->create();

    // Daily
    $p1 = Payable::factory()->state(['company_id' => $company->id, 'due_at' => '2025-01-01'])->create();
    $p1->recurring()->create(['company_id' => $company->id, 'frequency' => 'daily', 'interval' => 1, 'started_at' => '2025-01-01']);
    $p1->skipNext();
    $this->assertEquals('2025-01-02', $p1->due_at->format('Y-m-d'));

    // Weekly
    $p2 = Payable::factory()->state(['company_id' => $company->id, 'due_at' => '2025-01-01'])->create();
    $p2->recurring()->create(['company_id' => $company->id, 'frequency' => 'weekly', 'interval' => 1, 'started_at' => '2025-01-01']);
    $p2->skipNext();
    $this->assertEquals('2025-01-08', $p2->due_at->format('Y-m-d'));

    // Monthly
    $p3 = Payable::factory()->state(['company_id' => $company->id, 'due_at' => '2025-01-01'])->create();
    $p3->recurring()->create(['company_id' => $company->id, 'frequency' => 'monthly', 'interval' => 1, 'started_at' => '2025-01-01']);
    $p3->skipNext();
    $this->assertEquals('2025-02-01', $p3->due_at->format('Y-m-d'));

    // Yearly
    $p4 = Payable::factory()->state(['company_id' => $company->id, 'due_at' => '2025-01-01'])->create();
    $p4->recurring()->create(['company_id' => $company->id, 'frequency' => 'yearly', 'interval' => 1, 'started_at' => '2025-01-01']);
    $p4->skipNext();
    $this->assertEquals('2026-01-01', $p4->due_at->format('Y-m-d'));
});

test('Model scopeFilter and toArrayResponse work correctly', function () {
    $company = Company::factory()->create();
    $payable = Payable::factory()->state([
        'company_id' => $company->id,
        'title'      => 'Test Title',
        'due_at'     => '2025-01-01',
        'amount'     => 123.45
    ])->create();

    // scopeFilter
    $filtered = Payable::filter('Test')->get();
    $this->assertCount(1, $filtered);
    $this->assertEquals($payable->id, $filtered->first()->id);

    $notFiltered = Payable::filter('Nonexistent')->get();
    $this->assertCount(0, $notFiltered);

    // toArrayResponse
    $response = $payable->toArrayResponse();
    $this->assertEquals($payable->id, $response['id']);
    $this->assertEquals('2025-01-01', $response['due_at']);
    $this->assertEquals(123.45, $response['amount']);
    $this->assertEquals('Test Title', $response['title']);
});
