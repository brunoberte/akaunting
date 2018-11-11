<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReceivablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->date('due_at');
            $table->string('currency_code', '3');
            $table->double('amount', 12, 2);
            $table->string('title');
            $table->integer('customer_id');
            $table->integer('category_id');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index(['company_id', 'due_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('receivables');
    }
}
