<?php

namespace App\Events;

class ReceivableCreated
{
    public $invoice;

    /**
     * Create a new event instance.
     *
     * @param $receivable
     */
    public function __construct($receivable)
    {
        $this->invoice = $receivable;
    }
}
