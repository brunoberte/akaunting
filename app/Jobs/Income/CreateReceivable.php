<?php

namespace App\Jobs\Income;

use App\Events\ReceivableCreated;
use App\Models\Income\Receivable;
use App\Traits\DateTime;
use App\Traits\Uploads;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateReceivable
{
    use DateTime, Dispatchable, Uploads;

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
     * @return Receivable
     */
    public function handle()
    {
        $receivable = Receivable::create($this->request->input());

        // Upload attachment
        if ($this->request->file('attachment')) {
            $media = $this->getMedia($this->request->file('attachment'), 'invoices');

            $receivable->attachMedia($media, 'attachment');
        }

        // Recurring
        $receivable->createRecurring();

        // Fire the event to make it extensible
        event(new ReceivableCreated($receivable));

        return $receivable;
    }
}