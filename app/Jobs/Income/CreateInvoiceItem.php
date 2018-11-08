<?php

namespace App\Jobs\Income;

use App\Models\Common\Item;
use App\Models\Income\InvoiceItem;
use App\Notifications\Common\Item as ItemNotification;
use App\Notifications\Common\ItemReminder as ItemReminderNotification;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateInvoiceItem
{
    use Dispatchable;

    protected $data;

    protected $invoice;

    protected $discount;

    /**
     * Create a new job instance.
     *
     * @param  $data
     * @param  $invoice
     * @param  $discount
     */
    public function __construct($data, $invoice, $discount = null)
    {
        $this->data = $data;
        $this->invoice = $invoice;
        $this->discount = $discount;
    }

    /**
     * Execute the job.
     *
     * @return InvoiceItem
     */
    public function handle()
    {
        $item_sku = '';

        $item_id = !empty($this->data['item_id']) ? $this->data['item_id'] : 0;

        if (!empty($item_id)) {
            $item_object = Item::find($item_id);

            $this->data['name'] = $item_object->name;
            $item_sku = $item_object->sku;

            // Decrease stock (item sold)
            $item_object->quantity -= $this->data['quantity'];
            $item_object->save();

            if (setting('general.send_item_reminder')) {
                $item_stocks = explode(',', setting('general.schedule_item_stocks'));

                foreach ($item_stocks as $item_stock) {
                    if ($item_object->quantity == $item_stock) {
                        foreach ($item_object->company->users as $user) {
                            if (!$user->can('read-notifications')) {
                                continue;
                            }

                            $user->notify(new ItemReminderNotification($item_object));
                        }
                    }
                }
            }

            // Notify users if out of stock
            if ($item_object->quantity == 0) {
                foreach ($item_object->company->users as $user) {
                    if (!$user->can('read-notifications')) {
                        continue;
                    }

                    $user->notify(new ItemNotification($item_object));
                }
            }
        } elseif (!empty($this->data['sku'])) {
            $item_sku = $this->data['sku'];
        }

        $invoice_item = InvoiceItem::create([
            'company_id' => $this->invoice->company_id,
            'invoice_id' => $this->invoice->id,
            'item_id' => $item_id,
            'name' => str_limit($this->data['name'], 180, ''),
            'sku' => $item_sku,
            'quantity' => (double) $this->data['quantity'],
            'price' => (double) $this->data['price'],
            'total' => (double) $this->data['price'] * (double) $this->data['quantity'],
        ]);

        return $invoice_item;
    }
}