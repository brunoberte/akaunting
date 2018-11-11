<?php

namespace App\Events;

class ReceivableUpdated
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
