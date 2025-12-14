<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\TransferCreateRequest;
use App\Http\Requests\Transfers\TransferUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TransfersController extends Controller
{
    public function new(): Response
    {
        return Inertia::render(
            'transactions/transfer-form',
            [
                'record'       => fn() => new Transfer(),
                'account_list' => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
            ]
        );
    }

    public function edit(Transfer $transfer): Response
    {
        $payment = Payment::findOrFail($transfer->payment_id);
        $revenue = Revenue::findOrFail($transfer->revenue_id);

        $transfer['from_account_id'] = $payment->account_id;
        $transfer['to_account_id'] = $revenue->account_id;
        $transfer['transferred_at'] = Carbon::parse($payment->paid_at)->format('Y-m-d');
        $transfer['description'] = $payment->description;
        $transfer['amount'] = $payment->amount;
        $transfer['reference'] = $payment->reference;

        return Inertia::render(
            'transactions/transfer-form',
            [
                'record'       => $transfer,
                'account_list' => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
            ]
        );
    }

    public function update(Transfer $transfer, TransferUpdateRequest $request): RedirectResponse
    {
        $payment_currency_code = Account::where('id', $request['from_account_id'])->pluck('currency_code')->first();
        $revenue_currency_code = Account::where('id', $request['to_account_id'])->pluck('currency_code')->first();

        $payment = Payment::findOrFail($transfer->payment_id);
        $revenue = Revenue::findOrFail($transfer->revenue_id);

        $payment_request = [
            'company_id'    => $request['company_id'],
            'account_id'    => $request['from_account_id'],
            'paid_at'       => $request['transferred_at'],
            'currency_code' => $payment_currency_code,
            'currency_rate' => $currencies[$payment_currency_code],
            'amount'        => $request['amount'],
            'vendor_id'     => 0,
            'description'   => $request['description'],
            'category_id'   => Category::transfer(), // Transfer Category ID
            'reference'     => $request['reference'],
        ];
        $payment->update($payment_request);

        $revenue_request = [
            'company_id'    => $request['company_id'],
            'account_id'    => $request['to_account_id'],
            'paid_at'       => $request['transferred_at'],
            'currency_code' => $revenue_currency_code,
            'currency_rate' => $currencies[$revenue_currency_code],
            'amount'        => $request['amount'],
            'customer_id'   => 0,
            'description'   => $request['description'],
            'category_id'   => Category::transfer(), // Transfer Category ID
            'reference'     => $request['reference'],
        ];
        $revenue->update($revenue_request);

        $transfer_request = [
            'company_id' => $request['company_id'],
            'payment_id' => $payment->id,
            'revenue_id' => $revenue->id,
        ];
        $transfer->update($transfer_request);

        return to_route('transactions.index', ['account_id' => $request->get('from_account_id')]);
    }

    public function create(TransferCreateRequest $request): RedirectResponse
    {
        $payment_currency_code = Account::where('id', $request['from_account_id'])->pluck('currency_code')->first();
        $revenue_currency_code = Account::where('id', $request['to_account_id'])->pluck('currency_code')->first();

        $payment_request = [
            'company_id'    => $request['company_id'],
            'account_id'    => $request['from_account_id'],
            'paid_at'       => $request['transferred_at'],
            'currency_code' => $payment_currency_code,
            'currency_rate' => '1',
            'amount'        => $request['amount'],
            'vendor_id'     => 0,
            'description'   => $request['description'],
            'category_id'   => Category::transfer(), // Transfer Category ID
            'reference'     => $request['reference'],
        ];

        $payment = Payment::create($payment_request);

        $revenue_request = [
            'company_id'    => $request['company_id'],
            'account_id'    => $request['to_account_id'],
            'paid_at'       => $request['transferred_at'],
            'currency_code' => $revenue_currency_code,
            'currency_rate' => '1',
            'amount'        => $request['amount'],
            'customer_id'   => 0,
            'description'   => $request['description'],
            'category_id'   => Category::transfer(), // Transfer Category ID
            'reference'     => $request['reference'],
        ];

        $revenue = Revenue::create($revenue_request);

        $transfer_request = [
            'company_id' => $request['company_id'],
            'payment_id' => $payment->id,
            'revenue_id' => $revenue->id,
        ];

        Transfer::create($transfer_request);

        return to_route('transactions.index', ['account_id' => $request->get('account_id')]);
    }

    public function destroy(Transfer $transfer): RedirectResponse
    {
        $transfer->delete();
        return to_route('transactions.index', ['account_id' => $transfer->payment->account_id]);
    }
}
