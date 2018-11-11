<?php

namespace App\Events;

class PayableUpdated
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
