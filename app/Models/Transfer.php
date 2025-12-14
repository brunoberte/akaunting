<?php

namespace App\Models;

class Transfer extends AppModel
{
    protected $table = 'transfers';

    protected $fillable = ['company_id', 'payment_id', 'revenue_id'];

    public $sortable = ['payment.paid_at', 'payment.amount', 'payment.name', 'revenue.name'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'payment.account_id', 'id');
    }

    public function revenue()
    {
        return $this->belongsTo(Revenue::class);
    }

    public function revenueAccount()
    {
        return $this->belongsTo(Account::class, 'revenue.account_id', 'id');
    }

    public function getDynamicConvertedAmount($format = false)
    {
        return $this->dynamicConvert($this->default_currency_code, $this->amount, $this->currency_code, $this->currency_rate, $format);
    }

    public function getReverseConvertedAmount($format = false)
    {
        return $this->reverseConvert($this->amount, $this->currency_code, $this->currency_rate, $format);
    }

    public function getDivideConvertedAmount($format = false)
    {
        return $this->divide($this->amount, $this->currency_code, $this->currency_rate, $format);
    }
}
