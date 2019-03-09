<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Model;
use App\Models\Company\Company;
use App\Models\Expense\Bill;
use App\Models\Expense\BillItem;
use App\Models\Expense\BillTotal;

class CreateBillTotalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_totals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('bill_id');
            $table->string('code')->nullable();
            $table->string('name');
            $table->decimal('amount', 15, 4);
            $table->integer('sort_order');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
        });

        Model::unguard();

        $companies = Company::all();

        foreach ($companies as $company) {
            $bills = Bill::where('company_id', $company->id)->get();

            foreach ($bills as $bill) {
                $bill_items = BillItem::where('company_id', $company->id)->where('bill_id', $bill->id)->get();

                $sub_total = 0;

                foreach ($bill_items as $bill_item) {
                    $bill_item->total = $bill_item->price * $bill_item->quantity;

                    $bill_item->update();

                    $sub_total += $bill_item->price * $bill_item->quantity;
                }

                $bill->amount = $sub_total;

                $bill->update();

                // Added bill total sub total
                $bill_sub_total = [
                    'company_id' => $company->id,
                    'bill_id' => $bill->id,
                    'code' => 'sub_total',
                    'name' => 'bills.sub_total',
                    'amount' => $sub_total,
                    'sort_order' => 1,
                ];

                BillTotal::create($bill_sub_total);

                $sort_order = 2;

                // Added bill total total
                $bill_total = [
                    'company_id' => $company->id,
                    'bill_id' => $bill->id,
                    'code' => 'total',
                    'name' => 'bills.total',
                    'amount' => $sub_total,
                    'sort_order' => $sort_order,
                ];

                BillTotal::create($bill_total);
            }
        }

        Model::reguard();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bill_totals');
    }
}
