<?php

namespace App\Events;

class ReceivableCreated
{
    public $receivable;

    /**
     * Create a new event instance.
     *
     * @param $receivable
     */
    public function __construct($receivable)
    {
        $this->receivable = $receivable;
    }
}
