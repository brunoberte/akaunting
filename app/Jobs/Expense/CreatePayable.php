<?php

namespace App\Jobs\Expense;

use App\Events\PayableCreated;
use App\Models\Expense\Payable;
use App\Traits\Currencies;
use App\Traits\DateTime;
use App\Traits\Uploads;
use Illuminate\Foundation\Bus\Dispatchable;

class CreatePayable
{
    use Currencies, DateTime, Dispatchable, Uploads;

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
     * @return Payable
     */
    public function handle()
    {
        $receivable = Payable::create($this->request->input());

        // Upload attachment
        if ($this->request->file('attachment')) {
            $media = $this->getMedia($this->request->file('attachment'), 'invoices');

            $receivable->attachMedia($media, 'attachment');
        }

        // Recurring
        $receivable->createRecurring();

        // Fire the event to make it extensible
        event(new PayableCreated($receivable));

        return $receivable;
    }
}