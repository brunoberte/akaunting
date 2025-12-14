<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\PaymentCreateRequest;
use App\Http\Requests\Payments\PaymentUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentsController extends Controller
{
    public function new(Request $request): Response
    {
        $payment = new Payment();
        $payment->account_id = $request->get('account_id', '');
        $payment->paid_at = Carbon::today();
        return Inertia::render(
            'transactions/payment-form',
            [
                'payment'       => $payment,
                'vendor_list'   => fn() => Vendor::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->orderBy('name')->select(['id', 'name', 'type'])->get()->collect()->toArray(),
            ]
        );
    }

    public function edit(Payment $payment): Response
    {
        return Inertia::render(
            'transactions/payment-form',
            [
                'payment'       => $payment,
                'vendor_list'   => fn() => Vendor::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->orderBy('name')->select(['id', 'name', 'type'])->get()->collect()->toArray(),
            ]
        );
    }

    public function update(Payment $payment, PaymentUpdateRequest $request): RedirectResponse
    {
        $payment->update($request->validated());
        return to_route('transactions.index', ['account_id' => $request->get('account_id')]);
    }

    public function create(PaymentCreateRequest $request): RedirectResponse
    {
        Payment::create(array_merge(
            $request->validated(),
            ['currency_rate' => '0']
        ));
        return to_route('transactions.index', ['account_id' => $request->get('account_id')]);
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();
        return to_route('transactions.index', ['account_id' => $payment->account_id]);
    }
}
