<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexes extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['company_id', 'category_id'], 'idx_payments_company_category');
            $table->index(['company_id', 'account_id', 'category_id', 'paid_at'], 'idx_payments_company_account');
            $table->index(['company_id', 'currency_code', 'category_id', 'paid_at'], 'idx_payments_company_currency');
        });
        Schema::table('revenues', function (Blueprint $table) {
            $table->index(['company_id', 'category_id'], 'idx_revenues_company_category');
            $table->index(['company_id', 'account_id', 'category_id', 'paid_at'], 'idx_revenues_company_account');
            $table->index(['company_id', 'currency_code', 'category_id', 'paid_at'], 'idx_revenues_company_currency');
        });
        Schema::table('receivables', function (Blueprint $table) {
            $table->index(['company_id', 'category_id'], 'idx_receivables_company_category');
            $table->index(['company_id', 'account_id', 'category_id', 'due_at'], 'idx_receivables_company_account');
            $table->index(['company_id', 'currency_code', 'category_id', 'due_at'], 'idx_receivables_company_currency');
        });
        Schema::table('payables', function (Blueprint $table) {
            $table->index(['company_id', 'category_id'], 'idx_payables_company_category');
            $table->index(['company_id', 'account_id', 'category_id', 'due_at'], 'idx_payables_company_account');
            $table->index(['company_id', 'currency_code', 'category_id', 'due_at'], 'idx_payables_company_currency');
        });
    }
}
