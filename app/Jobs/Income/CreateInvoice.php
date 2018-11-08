<?php

namespace App\Jobs\Income;

use App\Events\InvoiceCreated;
use App\Models\Income\Invoice;
use App\Models\Income\InvoiceHistory;
use App\Models\Income\InvoiceTotal;
use App\Traits\Currencies;
use App\Traits\DateTime;
use App\Traits\Incomes;
use App\Traits\Uploads;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateInvoice
{
    use Currencies, DateTime, Dispatchable, Incomes, Uploads;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @param  $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return Invoice
     */
    public function handle()
    {
        $invoice = Invoice::create($this->request->input());

        // Upload attachment
        if ($this->request->file('attachment')) {
            $media = $this->getMedia($this->request->file('attachment'), 'invoices');

            $invoice->attachMedia($media, 'attachment');
        }

        $sub_total = 0;
        $discount_total = 0;
        $discount = $this->request['discount'];

        if ($this->request['item']) {
            foreach ($this->request['item'] as $item) {
                $invoice_item = dispatch(new CreateInvoiceItem($item, $invoice, $discount));

                // Calculate totals
                $sub_total += $invoice_item->total;
            }
        }

        $s_total = $sub_total;

        // Apply discount to total
        if ($discount) {
            $s_discount = $s_total * ($discount / 100);
            $discount_total += $s_discount;
            $s_total = $s_total - $s_discount;
        }

        $amount = $s_total;

        $this->request['amount'] = money($amount, $this->request['currency_code'])->getAmount();

        $invoice->update($this->request->input());

        // Add invoice totals
        $this->addTotals($invoice, $this->request, $sub_total, $discount_total);

        // Add invoice history
        InvoiceHistory::create([
            'company_id' => session('company_id'),
            'invoice_id' => $invoice->id,
            'status_code' => 'draft',
            'notify' => 0,
            'description' => trans('messages.success.added', ['type' => $invoice->invoice_number]),
        ]);

        // Update next invoice number
        $this->increaseNextInvoiceNumber();

        // Recurring
        $invoice->createRecurring();

        // Fire the event to make it extensible
        event(new InvoiceCreated($invoice));

        return $invoice;
    }

    protected function addTotals($invoice, $request, $sub_total, $discount_total)
    {
        $sort_order = 1;

        // Added invoice sub total
        InvoiceTotal::create([
            'company_id' => $request['company_id'],
            'invoice_id' => $invoice->id,
            'code' => 'sub_total',
            'name' => 'invoices.sub_total',
            'amount' => $sub_total,
            'sort_order' => $sort_order,
        ]);

        $sort_order++;

        // Added invoice discount
        if ($discount_total) {
            InvoiceTotal::create([
                'company_id' => $request['company_id'],
                'invoice_id' => $invoice->id,
                'code' => 'discount',
                'name' => 'invoices.discount',
                'amount' => $discount_total,
                'sort_order' => $sort_order,
            ]);

            // This is for total
            $sub_total = $sub_total - $discount_total;

            $sort_order++;
        }

        // Added invoice total
        InvoiceTotal::create([
            'company_id' => $request['company_id'],
            'invoice_id' => $invoice->id,
            'code' => 'total',
            'name' => 'invoices.total',
            'amount' => $sub_total,
            'sort_order' => $sort_order,
        ]);
    }
}