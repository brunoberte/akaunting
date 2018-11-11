<?php

namespace App\Events;

class PayableCreated
{
    public $payable;

    /**
     * Create a new event instance.
     *
     * @param $payable
     */
    public function __construct($payable)
    {
        $this->payable = $payable;
    }
}
