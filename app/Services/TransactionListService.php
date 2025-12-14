<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Revenue;
use Carbon\Carbon;

class TransactionListService
{
    public function getTransactionsForAccountOnPeriod(Account $account, Carbon $start_date, Carbon $end_date): array
    {
        $dates = [
            $start_date->clone()->startOfDay()->toDateTimeString(),
            $end_date->clone()->endOfDay()->toDateTimeString(),
        ];
        $initial_balance = $account->getBalanceOnDate($start_date->clone()->subDay());

        $payments = Payment::query()
            ->where('account_id', $account->id)
            ->whereBetween('paid_at', $dates)
            ->get();

        $revenues = Revenue::query()
            ->where('account_id', $account->id)
            ->whereBetween('paid_at', $dates)
            ->get();

        $balance = $initial_balance;
        $transactions = $payments->concat($revenues)
            ->sortBy('paid_at')
            ->map(function (Revenue|Payment $item) use ($balance) {
                $is_payment = get_class($item) === Payment::class;
                return [
                    'id'               => $item->id,
                    'type_description' => $this->getTypeDescription($item),
                    'paid_at'          => $item->paid_at->format('Y-m-d'),
                    'is_transfer'      => $item->is_transfer,
                    'credit'           => !$is_payment ? $item->amount : null,
                    'debit'            => $is_payment ? $item->amount : null,
                    'balance'          => $balance + (!$is_payment ? $item->amount : ($item->amount * -1)),
                    'currency_code'    => $item->currency_code,
                    'account_id'       => $item->account_id,
                    'account_name'     => $item->account->name,
                    'category_id'      => $item->category_id,
                    'category_name'    => $item->category?->name,
                    'vendor_id'        => $item->vendor_id,
                    'vendor_name'      => $item->vendor?->name,
                    'customer_id'      => $item->customer_id,
                    'customer_name'    => $item->customer?->name,
                    'description'      => $item->description,
                ];
            })
            ->collect()
            ->toArray();

        return array_values($transactions);
    }

    private function getTypeDescription(Payment|Revenue $item)
    {
        if ($item->is_transfer && $item->type === 'Revenue') {
            return sprintf('Transfer from %s', $item->transfer->payment->account->name);
        }
        if ($item->is_transfer && $item->type === 'Payment') {
            return sprintf('Transfer to %s', $item->transfer->revenue->account->name);
        }
        return match (get_class($item)) {
            Payment::class => 'Payment',
            Revenue::class => 'Revenue',
        };
    }
}
