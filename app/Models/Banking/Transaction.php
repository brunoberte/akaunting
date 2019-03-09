<?php

namespace App\Models\Banking;

use App\Models\Expense\Payment;
use App\Models\Income\Revenue;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public static function getUserTransactions($user_id, $type)
    {
        $transactions = array();

        switch ($type) {
            case 'payments':
                $payments = Payment::where('vendor_id', $user_id)->get();

                foreach ($payments as $payment) {
                    $transactions[] = (object) [
                        'date'          => $payment->paid_at,
                        'account'       => $payment->account->name,
                        'type'          => 'Expense',
                        'category'      => $payment->category->name,
                        'description'   => $payment->description,
                        'amount'        => $payment->amount,
                        'currency_code' => $payment->currency_code,
                    ];
                }
                break;
            case 'revenues':
                $revenues = Revenue::where('customer_id', $user_id)->get();

                foreach ($revenues as $revenue) {
                    $transactions[] = (object) [
                        'date'          => $revenue->paid_at,
                        'account'       => $revenue->account->name,
                        'type'          => trans_choice('general.payments', 1),
                        'category'      => $revenue->category->name,
                        'description'   => $revenue->description,
                        'amount'        => $revenue->amount,
                        'currency_code' => $revenue->currency_code,
                    ];
                }
                break;
        }

        return $transactions;
    }
}
