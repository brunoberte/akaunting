<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Model;
use App\Models\Company\Company;
use App\Models\Income\Invoice;
use App\Models\Income\InvoiceItem;
use App\Models\Income\InvoiceTotal;

class CreateInvoiceTotalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_totals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('invoice_id');
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
            $invoices = Invoice::where('company_id', $company->id)->get();

            foreach ($invoices as $invoice) {
                $invoice_items = InvoiceItem::where('company_id', $company->id)->where('invoice_id', $invoice->id)->get();

                $sub_total = 0;

                foreach ($invoice_items as $invoice_item) {
                    $invoice_item->total = $invoice_item->price * $invoice_item->quantity;

                    $invoice_item->update();

                    $sub_total += $invoice_item->price * $invoice_item->quantity;
                }

                $invoice->amount = $sub_total;

                $invoice->update();

                // Added invoice total sub total
                $invoice_sub_total = [
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'code' => 'sub_total',
                    'name' => 'invoices.sub_total',
                    'amount' => $sub_total,
                    'sort_order' => 1,
                ];

                InvoiceTotal::create($invoice_sub_total);

                $sort_order = 2;

                // Added invoice total total
                $invoice_total = [
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'code' => 'total',
                    'name' => 'invoices.total',
                    'amount' => $sub_total,
                    'sort_order' => $sort_order,
                ];

                InvoiceTotal::create($invoice_total);
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
        Schema::drop('invoice_totals');
    }
}
