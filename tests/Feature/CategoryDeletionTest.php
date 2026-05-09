<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\Receivable;
use App\Models\Revenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_deleted_if_not_in_use()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $category = Category::factory()->state(['company_id' => $company->id])->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['company_id' => $company->id])
            ->delete("/categories/{$category->id}");

        $response->assertRedirect('/categories');
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_category_cannot_be_deleted_if_has_payments()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'expense'])->create();

        Payment::factory()->state([
            'company_id'  => $company->id,
            'category_id' => $category->id,
        ])->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['company_id' => $company->id])
            ->delete("/categories/{$category->id}");

        $response->assertSessionHasErrors(['category']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_category_cannot_be_deleted_if_has_revenues()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'income'])->create();

        Revenue::factory()->state([
            'company_id'  => $company->id,
            'category_id' => $category->id,
        ])->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['company_id' => $company->id])
            ->delete("/categories/{$category->id}");

        $response->assertSessionHasErrors(['category']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_category_cannot_be_deleted_if_has_payables()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'expense'])->create();

        Payable::factory()->state([
            'company_id'  => $company->id,
            'category_id' => $category->id,
        ])->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['company_id' => $company->id])
            ->delete("/categories/{$category->id}");

        $response->assertSessionHasErrors(['category']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_category_cannot_be_deleted_if_has_receivables()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $category = Category::factory()->state(['company_id' => $company->id, 'type' => 'income'])->create();

        Receivable::factory()->state([
            'company_id'  => $company->id,
            'category_id' => $category->id,
        ])->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['company_id' => $company->id])
            ->delete("/categories/{$category->id}");

        $response->assertSessionHasErrors(['category']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }
}
